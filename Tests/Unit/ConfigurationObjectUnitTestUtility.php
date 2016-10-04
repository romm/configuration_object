<?php
namespace Romm\ConfigurationObject\Tests\Unit;

use Romm\ConfigurationObject\ConfigurationObjectFactory;
use Romm\ConfigurationObject\ConfigurationObjectMapper;
use Romm\ConfigurationObject\Core\Core;
use Romm\ConfigurationObject\TypeConverter\ConfigurationObjectConverter;
use Romm\ConfigurationObject\Validation\ValidatorResolver;
use TYPO3\CMS\Extbase\Object\Container\Container;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationBuilder;
use TYPO3\CMS\Extbase\Property\TypeConverter\ArrayConverter;
use TYPO3\CMS\Extbase\Property\TypeConverter\ObjectConverter;
use TYPO3\CMS\Extbase\Property\TypeConverter\StringConverter;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

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
                        $args = func_get_args();
                        $reflectionClass = new \ReflectionClass(array_shift($args));
                        if (empty($args)) {
                            $instance = $reflectionClass->newInstance();
                        } else {
                            $instance = $reflectionClass->newInstanceArgs($args);
                        }

                        return $instance;
                    }
                )
            );

        return $mockObjectManager;
    }
}
