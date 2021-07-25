<?php
namespace Romm\ConfigurationObject\Tests\Unit;

use Romm\ConfigurationObject\ConfigurationObjectFactory;
use Romm\ConfigurationObject\ConfigurationObjectMapper;
use Romm\ConfigurationObject\Core\Core;
use Romm\ConfigurationObject\Core\Service\CacheService as InternalCacheService;
use Romm\ConfigurationObject\Core\Service\ObjectService;
use Romm\ConfigurationObject\Reflection\ReflectionService;
use Romm\ConfigurationObject\Service\Items\Cache\CacheService;
use Romm\ConfigurationObject\Service\Items\Parents\ParentsUtility;
use Romm\ConfigurationObject\Service\ServiceFactory;
use Romm\ConfigurationObject\TypeConverter\ConfigurationObjectConverter;
use Romm\ConfigurationObject\Validation\ValidatorResolver;
use TYPO3\CMS\Core\Cache\Backend\TransientMemoryBackend;
use TYPO3\CMS\Core\Cache\CacheFactory;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Extbase\Object\Container\Container;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationBuilder;
use TYPO3\CMS\Extbase\Property\TypeConverter\ArrayConverter;
use TYPO3\CMS\Extbase\Property\TypeConverter\ObjectConverter;
use TYPO3\CMS\Extbase\Property\TypeConverter\StringConverter;
use TYPO3\CMS\Extbase\Service\TypeHandlingService;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

trait ConfigurationObjectUnitTestUtility
{

    /**
     * @var Core|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configurationObjectCoreMock;

    /**
     * @var array
     */
    protected static $defaultTypeConverters = [
        ArrayConverter::class,
        ObjectConverter::class,
        StringConverter::class
    ];

    /**
     * Use this function if you need to create a configuration object in your
     * unit tests. Just call it from you function `setUp()`.
     */
    protected function initializeConfigurationObjectTestServices()
    {
        // We need to register the type converters used in these examples.
        $list = ArrayUtility::isValidPath($GLOBALS, 'TYPO3_CONF_VARS.EXTCONF.extbase.typeConverters', '.')
            ? ArrayUtility::getValueByPath($GLOBALS, 'TYPO3_CONF_VARS.EXTCONF.extbase.typeConverters', '.')
            : [];

        foreach (self::$defaultTypeConverters as $converter) {
            if (false === in_array($converter, $list)) {
                ExtensionUtility::registerTypeConverter($converter);
            }
        }

        $this->setUpConfigurationObjectCore();
        $this->injectMockedValidatorResolverInCore();
        $this->injectMockedConfigurationObjectFactory();
    }

    /**
     * Initializes correctly this extension `Core` class to be able to work
     * correctly in unit tests.
     */
    private function setUpConfigurationObjectCore()
    {
        $this->configurationObjectCoreMock = $this->getConfigurationObjectMockBuilder(Core::class)
            ->setMethods(['getServiceFactoryInstance'])
            ->getMock();
        $this->configurationObjectCoreMock->injectObjectManager($this->getConfigurationObjectObjectManagerMock());
        $this->configurationObjectCoreMock->injectObjectService(new ObjectService);
        $this->configurationObjectCoreMock->injectParentsUtility(new ParentsUtility);

        $reflectionService = new ReflectionService;
        $reflectedProperty = new \ReflectionProperty($reflectionService, 'objectManager');
        $reflectedProperty->setAccessible(true);
        $reflectedProperty->setValue($reflectionService, $this->getConfigurationObjectObjectManagerMock());
        $this->configurationObjectCoreMock->injectReflectionService($reflectionService);

        $this->configurationObjectCoreMock->method('getServiceFactoryInstance')
            ->willReturnCallback(
                function () {
                    /** @var ServiceFactory|\PHPUnit_Framework_MockObject_MockObject $serviceFactoryMock */
                    $serviceFactoryMock = $this->getConfigurationObjectMockBuilder(ServiceFactory::class)
                        ->setMethods(['manageServiceData'])
                        ->getMock();
                    $serviceFactoryMock->method('manageServiceData')
                        ->willReturnCallback(
                            function (array $service) {
                                $className = $service['className'];
                                $options = $service['options'];

                                if (CacheService::class === $className) {
                                    $options[CacheService::OPTION_CACHE_BACKEND] = TransientMemoryBackend::class;
                                }

                                return [$className, $options];
                            }
                        );

                    return $serviceFactoryMock;
                }
            );

        $cacheManager = new CacheManager;

        $cacheManager->setCacheConfigurations([
            InternalCacheService::CACHE_IDENTIFIER => [
                'backend'  => TransientMemoryBackend::class,
                'frontend' => VariableFrontend::class
            ]
        ]);

        $this->configurationObjectCoreMock->injectCacheManager($cacheManager);
        $cacheService = new InternalCacheService;
//        $cacheService->registerInternalCache();
        $this->inject($cacheService, 'cacheManager', $cacheManager);

        $this->configurationObjectCoreMock->injectCacheService($cacheService);

        $reflectedCore = new \ReflectionClass(Core::class);
        $objectManagerProperty = $reflectedCore->getProperty('instance');
        $objectManagerProperty->setAccessible(true);
        $objectManagerProperty->setValue($this->configurationObjectCoreMock);
    }

    /**
     * Will force the Extbase `ValidatorResolver` getter of the core to return a
     * mocked instance of the class.
     */
    protected function injectMockedValidatorResolverInCore()
    {
        /** @var ValidatorResolver $validatorResolver */
        $validatorResolver = $this->getConfigurationObjectObjectManagerMock()->get(ValidatorResolver::class);

        $validatorResolver->injectObjectManager(Core::get()->getObjectManager());
        $validatorResolver->injectReflectionService(Core::get()->getReflectionService());

        $this->configurationObjectCoreMock->injectValidatorResolver($validatorResolver);
    }

    /**
     * This function will handle the whole creation of a mocked instance of
     * `ConfigurationObjectFactory`, and inject it in the property `$instance`
     * of the class.
     */
    protected function injectMockedConfigurationObjectFactory()
    {
        /** @var ConfigurationObjectMapper|\PHPUnit_Framework_MockObject_MockObject $mockedConfigurationObjectMapper */
        $mockedConfigurationObjectMapper = $this->getConfigurationObjectMockBuilder(ConfigurationObjectMapper::class)
            ->setMethods(['getObjectConverter'])
            ->getMock();

        $objectContainer = new Container();
        $configurationObjectConverter = new ConfigurationObjectConverter();

        $configurationObjectConverter->injectObjectContainer($objectContainer);
        $configurationObjectConverter->injectObjectManager(Core::get()->getObjectManager());

        $reflectionService = new \Romm\ConfigurationObject\Legacy\Reflection\ReflectionService();
        $reflectionService->injectObjectManager(Core::get()->getObjectManager());
        $reflectedProperty = new \ReflectionProperty($configurationObjectConverter, 'reflectionService');
        $reflectedProperty->setAccessible(true);
        $reflectedProperty->setValue($configurationObjectConverter, $reflectionService);

        $mockedConfigurationObjectMapper->expects($this->any())
            ->method('getObjectConverter')
            ->willReturn($configurationObjectConverter);

        $propertyMappingConfigurationBuilder = Core::get()->getObjectManager()->get(PropertyMappingConfigurationBuilder::class);
        $mockedConfigurationObjectMapper->injectConfigurationBuilder($propertyMappingConfigurationBuilder);
        $mockedConfigurationObjectMapper->injectObjectManager(Core::get()->getObjectManager());

        $reflectionService = Core::get()->getReflectionService();
        $reflectionService->injectObjectManager(Core::get()->getObjectManager());

        $mockedConfigurationObjectMapper->initializeObject();

        /** @var ConfigurationObjectFactory|\PHPUnit_Framework_MockObject_MockObject $mockedConfigurationObjectFactory */
        $mockedConfigurationObjectFactory = $this->getConfigurationObjectMockBuilder(ConfigurationObjectFactory::class)
            ->setMethods(['getConfigurationObjectMapper'])
            ->getMock();

        $mockedConfigurationObjectFactory->expects($this->any())
            ->method('getConfigurationObjectMapper')
            ->willReturn($mockedConfigurationObjectMapper);

        $reflectedClass = new \ReflectionClass(ConfigurationObjectFactory::class);
        $objectManagerProperty = $reflectedClass->getProperty('instance');
        $objectManagerProperty->setAccessible(true);
        $objectManagerProperty->setValue($mockedConfigurationObjectFactory);
    }

    /**
     * Returns a mocked instance of the Extbase `ObjectManager`. Will allow the
     * main function `get()` to work properly during the tests.
     *
     * @return ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getConfigurationObjectObjectManagerMock()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject $mockObjectManager */
        $mockObjectManager = $this->getConfigurationObjectMockBuilder(ObjectManagerInterface::class)
            ->getMock();
        $mockObjectManager->expects($this->any())
            ->method('get')
            ->willReturnCallback(
                function () {
                    $arguments = func_get_args();
                    $className = array_shift($arguments);

                    if (in_array(AbstractValidator::class, class_parents($className))) {
                        /** @var  AbstractValidator|\PHPUnit_Framework_MockObject_MockObject $instance */
                        $instance = $this->getConfigurationObjectMockBuilder($className)
                            ->setMethods(['translateErrorMessage'])
                            ->setConstructorArgs($arguments)
                            ->getMock();
                        $instance->expects($this->any())
                            ->method('translateErrorMessage')
                            ->willReturnCallback(
                                function ($key, $extension) {
                                    return 'LLL:' . $extension . ':' . $key;
                                }
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
            );

        return $mockObjectManager;
    }

    /**
     * Just a wrapper to have auto-completion.
     *
     * @param string $className
     * @return \PHPUnit_Framework_MockObject_MockBuilder
     */
    private function getConfigurationObjectMockBuilder($className)
    {
        return $this->getMockBuilder($className);
    }
}
