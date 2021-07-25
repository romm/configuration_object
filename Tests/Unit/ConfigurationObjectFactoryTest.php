<?php
namespace Romm\ConfigurationObject\Tests\Unit;

use PHPUnit\Framework\MockObject\MockObject;
use Prophecy\Comparator\Factory;
use Romm\ConfigurationObject\ConfigurationObjectFactory;
use Romm\ConfigurationObject\ConfigurationObjectInstance;
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

class ConfigurationObjectFactoryTest extends AbstractUnitTest
{

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

    protected function setUp(): void
    {
        parent::setUp();

        $this->initializeConfigurationObjectTestServices();
    }

    /**
     * The alias method for converting an object should call the implementation
     * correctly.
     *
     * @test
     */
    public function convertMethodsCallsImplementation()
    {
        $className = Factory::class;
        $objectData = ['foo' => 'bar'];

        /** @var ConfigurationObjectFactory|MockObject $factoryMock */
        $factoryMock = $this->getMockBuilder(ConfigurationObjectFactory::class)
            ->setMethods(['get'])
            ->getMock();

        $factoryMock->expects($this->once())
            ->method('get')
            ->with($className, $objectData);

        $reflection = new \ReflectionClass(ConfigurationObjectFactory::class);
        $objectManagerProperty = $reflection->getProperty('instance');
        $objectManagerProperty->setAccessible(true);
        $objectManagerProperty->setValue($factoryMock);

        ConfigurationObjectFactory::convert($className, $objectData);
    }

    /**
     * Trying to create a configuration object with a not existing class name
     * must throw an exception.
     *
     * @test
     */
    public function createObjectWithWrongClassNameThrowsException()
    {
        $this->expectException(ClassNotFoundException::class);
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
        $this->expectException(WrongInheritanceException::class);
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
        $this->expectException(WrongServiceException::class);
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
        $this->expectException(EntryNotFoundException::class);
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

        $this->assertInstanceOf(ConfigurationObjectInstance::class, $companyObject);
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

        $this->expectException(Exception::class);
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
     * Checks that the mixed-types resolver is called and works correctly.
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

    /**
     * Checks that the configuration object factory process can be checked.
     *
     * @test
     */
    public function configurationObjectFactoryIsRunning()
    {
        /** @var ConfigurationObjectFactory|MockObject $factory */
        $factory = $this->getMockBuilder(ConfigurationObjectFactory::class)
            ->setMethods(['convertToObject'])
            ->getMock();

        $factory->expects($this->once())
            ->method('convertToObject')
            ->willReturnCallback(function () use ($factory) {
                $this->assertTrue($factory->isRunning());
            });

        $this->assertFalse($factory->isRunning());
        $factory->get(Company::class, []);
        $this->assertFalse($factory->isRunning());
    }
}
