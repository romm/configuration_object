<?php
namespace Romm\ConfigurationObject\Tests\Unit;

use Romm\ConfigurationObject\ConfigurationObjectFactory;
use Romm\ConfigurationObject\ConfigurationObjectMapper;
use Romm\ConfigurationObject\Core\Core;
use Romm\ConfigurationObject\TypeConverter\ConfigurationObjectConverter;
use Romm\ConfigurationObject\Validation\ValidatorResolver;
use TYPO3\CMS\Core\Cache\CacheFactory;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Extbase\Object\Container\Container;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationBuilder;
use TYPO3\CMS\Extbase\Property\TypeConverter\ArrayConverter;
use TYPO3\CMS\Extbase\Property\TypeConverter\ObjectConverter;
use TYPO3\CMS\Extbase\Property\TypeConverter\StringConverter;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

trait ConfigurationObjectUnitTestUtility
{

    /**
     * Use this function if you need to create a configuration object in your
     * unit tests. Just call it from you function `setUp()`.
     */
    public function initializeConfigurationObjectTestServices()
    {
        // We need to register the type converters used in these examples.
        ExtensionUtility::registerTypeConverter(ArrayConverter::class);
        ExtensionUtility::registerTypeConverter(ObjectConverter::class);
        ExtensionUtility::registerTypeConverter(StringConverter::class);

        $this->injectMockedObjectManagerInCore();
        $this->injectMockedValidatorResolverInCore();
        $this->injectMockedConfigurationObjectFactory();
        $this->injectCacheManagerInCore();
    }

    /**
     * Will force the Extbase `ObjectManager` getter of the core to return a
     * mocked instance of the class.
     */
    public function injectMockedObjectManagerInCore()
    {
        $reflectedCore = new \ReflectionClass(Core::class);
        $objectManagerProperty = $reflectedCore->getProperty('objectManager');
        $objectManagerProperty->setAccessible(true);
        $objectManagerProperty->setValue($this->getObjectManagerMock());
    }

    /**
     * Will force the Extbase `ValidatorResolver` getter of the core to return a
     * mocked instance of the class.
     */
    public function injectMockedValidatorResolverInCore()
    {
        $validatorResolver = $this->getObjectManagerMock()->get(ValidatorResolver::class);

        $validatorResolver->injectObjectManager(Core::getObjectManager());
        $validatorResolver->injectReflectionService(Core::getReflectionService());

        $reflectedCore = new \ReflectionClass(Core::class);
        $objectManagerProperty = $reflectedCore->getProperty('validatorResolver');
        $objectManagerProperty->setAccessible(true);
        $objectManagerProperty->setValue($validatorResolver);
    }

    /**
     * Will inject an instance of `CacheManager` in the core, which will be used
     * later on by objects like the `CacheService`.
     */
    public function injectCacheManagerInCore()
    {
        $cacheManager = new CacheManager;
        $cacheFactory = new CacheFactory('foo', $cacheManager);
        $cacheManager->injectCacheFactory($cacheFactory);

        $reflectedCore = new \ReflectionClass(Core::class);
        $objectManagerProperty = $reflectedCore->getProperty('cacheManager');
        $objectManagerProperty->setAccessible(true);
        $objectManagerProperty->setValue($cacheManager);
    }

    /**
     * This function will handle the whole creation of a mocked instance of
     * `ConfigurationObjectFactory`, and inject it in the property `$instance`
     * of the class.
     */
    public function injectMockedConfigurationObjectFactory()
    {
        /** @var ConfigurationObjectMapper|\PHPUnit_Framework_MockObject_MockObject $mockedConfigurationObjectMapper */
        $mockedConfigurationObjectMapper = $this->getMock(ConfigurationObjectMapper::class, ['getObjectConverter']);

        $configurationObjectConverter = new ConfigurationObjectConverter();
        $objectContainer = new Container();
        /** @var ConfigurationObjectConverter $configurationObjectConverter */
        $configurationObjectConverter->injectObjectContainer($objectContainer);
        $configurationObjectConverter->injectObjectManager(Core::getObjectManager());
        $configurationObjectConverter->injectReflectionService(Core::getReflectionService());

        $mockedConfigurationObjectMapper->expects($this->any())
            ->method('getObjectConverter')
            ->will($this->returnValue($configurationObjectConverter));

        $propertyMappingConfigurationBuilder = Core::getObjectManager()->get(PropertyMappingConfigurationBuilder::class);
        $mockedConfigurationObjectMapper->injectConfigurationBuilder($propertyMappingConfigurationBuilder);
        $mockedConfigurationObjectMapper->injectObjectManager(Core::getObjectManager());

        $reflectionService = Core::getReflectionService();
        $reflectionService->injectObjectManager(Core::getObjectManager());
        $mockedConfigurationObjectMapper->injectReflectionService($reflectionService);

        $mockedConfigurationObjectMapper->initializeObject();

        /** @var ConfigurationObjectFactory|\PHPUnit_Framework_MockObject_MockObject $mockedConfigurationObjectFactory */
        $mockedConfigurationObjectFactory = $this->getMock(ConfigurationObjectFactory::class, ['getConfigurationObjectMapper']);

        $mockedConfigurationObjectFactory->expects($this->any())
            ->method('getConfigurationObjectMapper')
            ->will($this->returnValue($mockedConfigurationObjectMapper));

        $reflectedCore = new \ReflectionClass(ConfigurationObjectFactory::class);
        $objectManagerProperty = $reflectedCore->getProperty('instance');
        $objectManagerProperty->setAccessible(true);
        $objectManagerProperty->setValue($mockedConfigurationObjectFactory);
    }

    /**
     * Returns a mocked instance of the Extbase `ObjectManager`. Will allow the
     * main function `get()` to work properly during the tests.
     *
     * @return ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getObjectManagerMock()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject $mockObjectManager */
        $mockObjectManager = $this->getMock(ObjectManagerInterface::class);
        $mockObjectManager->expects($this->any())
            ->method('get')
            ->will(
                $this->returnCallback(
                    function () {
                        $arguments = func_get_args();
                        $className = array_shift($arguments);

                        if (in_array(AbstractValidator::class, class_parents($className))) {
                            /** @var  AbstractValidator|\PHPUnit_Framework_MockObject_MockObject $instance */
                            $instance = $this->getMock($className, ['translateErrorMessage'], $arguments);
                            $instance->expects($this->any())
                                ->method('translateErrorMessage')
                                ->will(
                                    $this->returnCallback(function($key, $extension) {
                                        return 'LLL:' . $extension . ':' . $key;
                                    })
                                );
                        } else {
                            $reflectionClass = new \ReflectionClass($className);
                            if (empty($arguments)) {
                                $instance = $reflectionClass->newInstance();
                            } else {
                                $instance = $reflectionClass->newInstanceArgs($arguments);
                            }
                        }

                        return $instance;
                    }
                )
            );

        return $mockObjectManager;
    }
}
