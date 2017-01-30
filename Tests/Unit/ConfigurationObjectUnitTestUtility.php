<?php
namespace Romm\ConfigurationObject\Tests\Unit;

use Romm\ConfigurationObject\ConfigurationObjectFactory;
use Romm\ConfigurationObject\ConfigurationObjectMapper;
use Romm\ConfigurationObject\Core\Core;
use Romm\ConfigurationObject\Service\Items\Cache\CacheService;
use Romm\ConfigurationObject\Service\Items\Parents\ParentsUtility;
use Romm\ConfigurationObject\Service\ServiceFactory;
use Romm\ConfigurationObject\TypeConverter\ConfigurationObjectConverter;
use Romm\ConfigurationObject\Validation\ValidatorResolver;
use TYPO3\CMS\Core\Cache\Backend\TransientMemoryBackend;
use TYPO3\CMS\Core\Cache\CacheFactory;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Object\Container\Container;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationBuilder;
use TYPO3\CMS\Extbase\Property\TypeConverter\ArrayConverter;
use TYPO3\CMS\Extbase\Property\TypeConverter\ObjectConverter;
use TYPO3\CMS\Extbase\Property\TypeConverter\StringConverter;
use TYPO3\CMS\Extbase\Reflection\ClassSchema;
use TYPO3\CMS\Extbase\Reflection\ReflectionService;
use TYPO3\CMS\Extbase\Service\EnvironmentService;
use TYPO3\CMS\Extbase\Service\TypeHandlingService;
use TYPO3\CMS\Extbase\Utility\ArrayUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;
use TYPO3\CMS\Extbase\Validation\Validator\GenericObjectValidator;

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
        $list = ArrayUtility::getValueByPath($GLOBALS, 'TYPO3_CONF_VARS.EXTCONF.extbase.typeConverters') ?: [];

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
        $this->configurationObjectCoreMock = $this->getMock(Core::class, ['getServiceFactoryInstance']);
        $this->configurationObjectCoreMock->injectObjectManager($this->getConfigurationObjectObjectManagerMock());
        $this->configurationObjectCoreMock->injectReflectionService(new ReflectionService);
        $this->configurationObjectCoreMock->injectValidatorResolver(new ValidatorResolver);
        $this->configurationObjectCoreMock->injectParentsUtility(new ParentsUtility);

        $this->configurationObjectCoreMock->method('getServiceFactoryInstance')
            ->will(
                $this->returnCallback(
                    function () {
                        /** @var ServiceFactory|\PHPUnit_Framework_MockObject_MockObject $serviceFactoryMock */
                        $serviceFactoryMock = $this->getMock(ServiceFactory::class, ['manageServiceData']);
                        $serviceFactoryMock->method('manageServiceData')
                            ->will(
                                $this->returnCallback(
                                    function (array $service) {
                                        $className = $service['className'];
                                        $options = $service['options'];

                                        if (CacheService::class === $className) {
                                            $options[CacheService::OPTION_CACHE_BACKEND] = TransientMemoryBackend::class;
                                        }

                                        return [$className, $options];
                                    }
                                )
                            );

                        return $serviceFactoryMock;
                    }
                )
            );

        $cacheManager = new CacheManager;
        $cacheFactory = new CacheFactory('foo', $cacheManager);
        $cacheManager->injectCacheFactory($cacheFactory);
        $this->configurationObjectCoreMock->injectCacheManager($cacheManager);

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

        if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '7.6.0', '<')) {
            $reflectedProperty = new \ReflectionProperty($validatorResolver, 'objectManager');
            $reflectedProperty->setAccessible(true);
            $reflectedProperty->setValue($validatorResolver, Core::get()->getObjectManager());

            $reflectedProperty = new \ReflectionProperty($validatorResolver, 'reflectionService');
            $reflectedProperty->setAccessible(true);
            $reflectedProperty->setValue($validatorResolver, Core::get()->getReflectionService());
        } else {
            $validatorResolver->injectObjectManager(Core::get()->getObjectManager());
            $validatorResolver->injectReflectionService(Core::get()->getReflectionService());
        }

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
        $mockedConfigurationObjectMapper = $this->getMock(ConfigurationObjectMapper::class, ['getObjectConverter']);

        $objectContainer = new Container();
        $configurationObjectConverter = new ConfigurationObjectConverter();
        if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '7.6.0', '<')) {
            $reflectedProperty = new \ReflectionProperty($configurationObjectConverter, 'objectContainer');
            $reflectedProperty->setAccessible(true);
            $reflectedProperty->setValue($configurationObjectConverter, $objectContainer);

            $reflectedProperty = new \ReflectionProperty($configurationObjectConverter, 'objectManager');
            $reflectedProperty->setAccessible(true);
            $reflectedProperty->setValue($configurationObjectConverter, Core::get()->getObjectManager());

            $reflectionService = Core::get()->getReflectionService();
            $reflectedProperty = new \ReflectionProperty($configurationObjectConverter, 'reflectionService');
            $reflectedProperty->setAccessible(true);
            $reflectedProperty->setValue($configurationObjectConverter, $reflectionService);

            $reflectedProperty = new \ReflectionProperty($configurationObjectConverter, 'reflectionService');
            $reflectedProperty->setAccessible(true);
            $reflectedProperty->setValue($configurationObjectConverter, $reflectionService);
        } else {
            $configurationObjectConverter->injectObjectContainer($objectContainer);
            $configurationObjectConverter->injectObjectManager(Core::get()->getObjectManager());
            $configurationObjectConverter->injectReflectionService(Core::get()->getReflectionService());
        }

        $mockedConfigurationObjectMapper->expects($this->any())
            ->method('getObjectConverter')
            ->will($this->returnValue($configurationObjectConverter));

        $propertyMappingConfigurationBuilder = Core::get()->getObjectManager()->get(PropertyMappingConfigurationBuilder::class);
        if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '7.6.0', '<')) {
            $reflectedProperty = new \ReflectionProperty($mockedConfigurationObjectMapper, 'configurationBuilder');
            $reflectedProperty->setAccessible(true);
            $reflectedProperty->setValue($mockedConfigurationObjectMapper, $propertyMappingConfigurationBuilder);

            $reflectedProperty = new \ReflectionProperty($mockedConfigurationObjectMapper, 'objectManager');
            $reflectedProperty->setAccessible(true);
            $reflectedProperty->setValue($mockedConfigurationObjectMapper, Core::get()->getObjectManager());
        } else {
            $mockedConfigurationObjectMapper->injectConfigurationBuilder($propertyMappingConfigurationBuilder);
            $mockedConfigurationObjectMapper->injectObjectManager(Core::get()->getObjectManager());
        }

        $reflectionService = Core::get()->getReflectionService();
        if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '7.6.0', '<')) {
            $reflectedProperty = new \ReflectionProperty($reflectionService, 'objectManager');
            $reflectedProperty->setAccessible(true);
            $reflectedProperty->setValue($reflectionService, Core::get()->getObjectManager());
        } else {
            $reflectionService->injectObjectManager(Core::get()->getObjectManager());
        }
        $mockedConfigurationObjectMapper->injectReflectionService($reflectionService);

        $mockedConfigurationObjectMapper->initializeObject();

        /** @var ConfigurationObjectFactory|\PHPUnit_Framework_MockObject_MockObject $mockedConfigurationObjectFactory */
        $mockedConfigurationObjectFactory = $this->getMock(ConfigurationObjectFactory::class, ['getConfigurationObjectMapper']);

        $mockedConfigurationObjectFactory->expects($this->any())
            ->method('getConfigurationObjectMapper')
            ->will($this->returnValue($mockedConfigurationObjectMapper));

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
                                    $this->returnCallback(
                                        function ($key, $extension) {
                                            return 'LLL:' . $extension . ':' . $key;
                                        }
                                    )
                                );

                            if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '7.6.0', '<')
                                && (
                                    GenericObjectValidator::class === $className
                                    || in_array(GenericObjectValidator::class, class_parents($className))
                                )
                            ) {
                                /** @var ConfigurationManager|\PHPUnit_Framework_MockObject_MockObject $configurationManager */
                                $configurationManager = $this->getMock(ConfigurationManager::class, ['isFeatureEnabled']);
                                $configurationManager->method('isFeatureEnabled')
                                    ->willReturn(true);

                                $reflectedProperty = new \ReflectionProperty($configurationManager, 'objectManager');
                                $reflectedProperty->setAccessible(true);
                                $reflectedProperty->setValue($configurationManager, Core::get()->getObjectManager());

                                /** @var EnvironmentService|\PHPUnit_Framework_MockObject_MockObject $environmentServiceMock */
                                $environmentServiceMock = $this->getMock(EnvironmentService::class, ['isEnvironmentInFrontendMode', 'isEnvironmentInBackendMode']);
                                $environmentServiceMock
                                    ->method('isEnvironmentInFrontendMode')
                                    ->willReturn(true);
                                $environmentServiceMock
                                    ->method('isEnvironmentInBackendMode')
                                    ->willReturn(false);

                                $reflectedProperty = new \ReflectionProperty($configurationManager, 'environmentService');
                                $reflectedProperty->setAccessible(true);
                                $reflectedProperty->setValue($configurationManager, $environmentServiceMock);

                                $configurationManager->initializeObject();

                                /** @var GenericObjectValidator $instance */
                                $instance->injectConfigurationManager($configurationManager);
                            }
                        } else {
                            $reflectionClass = new \ReflectionClass($className);
                            if (empty($arguments)) {
                                $instance = $reflectionClass->newInstance();
                            } else {
                                $instance = $reflectionClass->newInstanceArgs($arguments);
                            }

                            if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '7.6.0', '<')) {
                                if ($className === ClassSchema::class) {
                                    $reflectedProperty = new \ReflectionProperty($instance, 'typeHandlingService');
                                    $reflectedProperty->setAccessible(true);
                                    $reflectedProperty->setValue($instance, new TypeHandlingService());
                                }
                            }
                        }

                        return $instance;
                    }
                )
            );

        return $mockObjectManager;
    }
}
