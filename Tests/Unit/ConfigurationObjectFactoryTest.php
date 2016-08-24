<?php
namespace Romm\ConfigurationObject\Tests\Unit;

use Romm\ConfigurationObject\ConfigurationObjectFactory;
use Romm\ConfigurationObject\ConfigurationObjectInstance;
use Romm\ConfigurationObject\ConfigurationObjectMapper;
use Romm\ConfigurationObject\Core\Core;
use Romm\ConfigurationObject\Exceptions\ClassNotFoundException;
use Romm\ConfigurationObject\Exceptions\EntryNotFoundException;
use Romm\ConfigurationObject\Exceptions\Exception;
use Romm\ConfigurationObject\Exceptions\WrongInheritanceException;
use Romm\ConfigurationObject\Service\WrongServiceException;
use Romm\ConfigurationObject\Tests\Fixture\Company\AnotherEmployee;
use Romm\ConfigurationObject\Tests\Fixture\Company\Company;
use Romm\ConfigurationObject\Tests\Fixture\Company\Employee;
use Romm\ConfigurationObject\Tests\Fixture\Model\DummyConfigurationObjectWithWrongServiceFactory;
use Romm\ConfigurationObject\Tests\Fixture\Validator\WrongValueValidator;
use Romm\ConfigurationObject\TypeConverter\ConfigurationObjectConverter;
use TYPO3\CMS\Core\Tests\UnitTestCase;
use TYPO3\CMS\Extbase\Object\Container\Container;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationBuilder;
use TYPO3\CMS\Extbase\Property\TypeConverter\ArrayConverter;
use TYPO3\CMS\Extbase\Property\TypeConverter\ObjectConverter;
use TYPO3\CMS\Extbase\Property\TypeConverter\StringConverter;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

class ConfigurationObjectFactoryTest extends UnitTestCase
{

    use UnitTestUtility;

    /**
     * @var Company
     */
    protected $company;

    /**
     * @var array
     */
    protected $defaultCompanyValues = [
        'name'      => 'My Company',
        'employees' => [
            'john.doe' => [
                'name'   => 'John Doe',
                'gender' => 'Male',
                'email'  => 'john.doe@my-company.com'
            ],
            'jane.doe' => [
                'name'   => 'Jane Doe',
                'gender' => 'Female',
                'email'  => 'jane.doe@my-company.com'
            ]
        ]
    ];

    protected function setUp()
    {
        // We need to register the type converters used in these examples.
        ExtensionUtility::registerTypeConverter(ArrayConverter::class);
        ExtensionUtility::registerTypeConverter(ObjectConverter::class);
        ExtensionUtility::registerTypeConverter(StringConverter::class);

        $this->injectMockedObjectManagerInCore();
        $this->injectMockedValidatorResolverInCore();
        $this->setMockedConfigurationObjectFactory();
    }

    /**
     * This function will handle the whole creation of a mocked instance of
     * `ConfigurationObjectFactory`.
     *
     * @return ConfigurationObjectFactory
     */
    protected function setMockedConfigurationObjectFactory()
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

        $mockedConfigurationObjectFactory = $this->getMock(ConfigurationObjectFactory::class, ['getConfigurationObjectMapper']);

        $mockedConfigurationObjectFactory->expects($this->any())
            ->method('getConfigurationObjectMapper')
            ->will($this->returnValue($mockedConfigurationObjectMapper));

        $reflectedCore = new \ReflectionClass(ConfigurationObjectFactory::class);
        $objectManagerProperty = $reflectedCore->getProperty('instance');
        $objectManagerProperty->setAccessible(true);
        $objectManagerProperty->setValue($mockedConfigurationObjectFactory);

        return $mockedConfigurationObjectFactory;
    }

    /**
     * Trying to create a configuration object with a not existing class name
     * must throw an exception.
     *
     * @test
     */
    public function createObjectWithWrongClassNameThrowsException()
    {
        $this->setExpectedException(ClassNotFoundException::class);
        ConfigurationObjectFactory::getInstance()
            ->get('dummyClass', []);
    }

    /**
     * Trying to create a configuration object with a class which does not
     * implement the interface `ConfigurationObjectInterface` must throw an
     * exception.
     *
     * @test
     */
    public function createObjectWithClassNameWithoutInterfaceThrowsException()
    {
        $this->setExpectedException(WrongInheritanceException::class);
        ConfigurationObjectFactory::getInstance()
            ->get(\stdClass::class, []);
    }

    /**
     * Creating a configuration object which is not able to return a correct
     * instance of `ServiceFactory` must throw an exception.
     *
     * @test
     */
    public function createObjectWithWrongServiceFactoryThrowsException()
    {
        $this->setExpectedException(WrongServiceException::class);
        ConfigurationObjectFactory::getInstance()
            ->get(DummyConfigurationObjectWithWrongServiceFactory::class, []);
    }

    /**
     * Trying to get the `ServiceFactory` of an unregistered configuration
     * object must throw an exception.
     *
     * @test
     */
    public function getConfigurationObjectServiceFactoryFromUnregisteredObjectThrowsException()
    {
        $this->setExpectedException(EntryNotFoundException::class);
        ConfigurationObjectFactory::getInstance()
            ->getConfigurationObjectServiceFactory('UnregisteredClassName');
    }

    /**
     * Will test the creation of a valid company object.
     *
     * @test
     */
    public function createCompanyObjectWithCorrectValues()
    {
        $companyObject = ConfigurationObjectFactory::getInstance()
            ->get(Company::class, $this->defaultCompanyValues);

        $this->assertTrue($companyObject instanceof ConfigurationObjectInstance);
        $this->assertFalse($companyObject->getValidationResult()->hasErrors());

        /** @var Company $company */
        $company = $companyObject->getObject();

        $this->assertEquals('My Company', $company->getName());

        return $companyObject;
    }

    /**
     * Will test the creation of a company with a value considered as wrong. The
     * result should be an object containing exactly one error.
     *
     * @test
     */
    public function createCompanyObjectWithWrongValues()
    {
        $values = $this->defaultCompanyValues;
        // Overriding the name with a wrong value which wont pass the validators.
        $values['employees']['john.doe']['name'] = WrongValueValidator::WRONG_VALUE;

        $companyObject = ConfigurationObjectFactory::getInstance()
            ->get(Company::class, $values);

        $this->assertTrue($companyObject->getValidationResult()->hasErrors());
        $this->assertEquals(1, count($companyObject->getValidationResult()->getFlattenedErrors()));

        $this->setExpectedException(Exception::class);
        $companyObject->getObject();
    }

    /**
     * Checks that the data pre-processor is called and works correctly.
     *
     * @test
     */
    public function createCompanyObjectWithDataPreProcessor()
    {
        $values = $this->defaultCompanyValues;
        /*
         * Overriding the name with a value which will be detected by the
         * `DataPreProcessor`. The new value will be changed with
         * `Employee::NAME_BAR`.
         */
        $values['employees']['john.doe']['name'] = Employee::NAME_FOO;

        $companyObject = ConfigurationObjectFactory::getInstance()
            ->get(Company::class, $values);

        $this->assertFalse($companyObject->getValidationResult()->hasErrors());

        /** @var Company $company */
        $company = $companyObject->getObject();
        $employees = $company->getEmployees();

        $this->assertEquals(Employee::NAME_BAR, $employees['john.doe']->getName());
    }

    /**
     * Checks that the mixed-types resolver is called and workd correctly.
     *
     * @test
     */
    public function createCompanyObjectWithMixedTypes()
    {
        $values = $this->defaultCompanyValues;
        /*
         * Adding the following key will be detected by the
         * `MixedTypesResolver`. The new object type will then be changed from
         * `Employee` to `AnotherEmployee`.
         */
        $values['employees']['john.doe'][Employee::KEY_ANOTHER_EMPLOYEE] = true;

        $companyObject = ConfigurationObjectFactory::getInstance()
            ->get(Company::class, $values);

        $this->assertFalse($companyObject->getValidationResult()->hasErrors());

        /** @var Company $company */
        $company = $companyObject->getObject();
        $employees = $company->getEmployees();

        $this->assertEquals(Employee::class, get_class($employees['jane.doe']));
        $this->assertEquals(AnotherEmployee::class, get_class($employees['john.doe']));
    }
}
