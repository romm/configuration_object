<?php
namespace Romm\ConfigurationObject\Tests\Unit\Service\DataTransferObject;

use Romm\ConfigurationObject\Exceptions\ClassNotFoundException;
use Romm\ConfigurationObject\Exceptions\WrongInheritanceException;
use Romm\ConfigurationObject\Service\DataTransferObject\AbstractServiceDTO;
use Romm\ConfigurationObject\Service\ServiceFactory;
use Romm\ConfigurationObject\Tests\Fixture\Company\Company;
use Romm\ConfigurationObject\Tests\Unit\AbstractUnitTest;

class AbstractServiceDTOTest extends AbstractUnitTest
{
    const CONFIGURATION_OBJECT_TEST_CLASS = Company::class;

    /**
     * @var AbstractServiceDTO
     */
    protected $abstractServiceDTO;

    /**
     * @var ServiceFactory
     */
    protected $serviceFactory;

    protected function setUp()
    {
        parent::setUp();

        /*
         * We instantiate a service factory and a mocked abstract service DTO,
         * which can then be used in the several test cases all along the class.
         */
        $this->serviceFactory = ServiceFactory::getInstance();
        $this->abstractServiceDTO = $this->getMockForAbstractClass(
            AbstractServiceDTO::class,
            [self::CONFIGURATION_OBJECT_TEST_CLASS, $this->serviceFactory]
        );
    }

    /**
     * Will test if the constructor of a DTO class returns a correct exception
     * if the given configuration object class name is not valid.
     *
     * @test
     */
    public function constructorThrowsExceptionOnWrongClassName()
    {
        $this->setExpectedException(ClassNotFoundException::class);
        $this->getMockForAbstractClass(AbstractServiceDTO::class, ['Some wrong class name', $this->serviceFactory]);
    }

    /**
     * Will test if the given configuration object class name respects the
     * inheritance condition.
     *
     * @test
     */
    public function constructorThrowsExceptionOnWrongInheritance()
    {
        $this->setExpectedException(WrongInheritanceException::class);
        $this->getMockForAbstractClass(AbstractServiceDTO::class, [\stdClass::class, $this->serviceFactory]);
    }

    /**
     * Will test if the configuration object class name is correctly stored.
     *
     * @test
     */
    public function theConstructorSetsTheConfigurationObjectClassNameCorrectly()
    {
        $this->assertEquals(
            self::CONFIGURATION_OBJECT_TEST_CLASS,
            $this->abstractServiceDTO->getConfigurationObjectClassName()
        );
    }

    /**
     * Will test if the service factory instance is correctly stored.
     *
     * @test
     */
    public function theConstructorSetsTheServiceFactoryCorrectly()
    {
        $this->assertEquals(
            $this->serviceFactory,
            $this->abstractServiceDTO->getServiceFactory()
        );
    }
}
