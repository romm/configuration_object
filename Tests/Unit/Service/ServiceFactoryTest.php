<?php
namespace Romm\ConfigurationObject\Tests\Unit\Service;

use Romm\ConfigurationObject\Exceptions\DuplicateEntryException;
use Romm\ConfigurationObject\Exceptions\EntryNotFoundException;
use Romm\ConfigurationObject\Exceptions\Exception;
use Romm\ConfigurationObject\Exceptions\InitializationNotSetException;
use Romm\ConfigurationObject\Exceptions\WrongInheritanceException;
use Romm\ConfigurationObject\Service\DataTransferObject\GetConfigurationObjectDTO;
use Romm\ConfigurationObject\Service\Items\Cache\CacheService;
use Romm\ConfigurationObject\Service\Items\Parents\ParentsService;
use Romm\ConfigurationObject\Service\ServiceFactory;
use Romm\ConfigurationObject\Tests\Fixture\Model\DummyConfigurationObject;
use Romm\ConfigurationObject\Tests\Unit\AbstractUnitTest;
use Romm\ConfigurationObject\Tests\Unit\Service\Fixture\DummyService;
use TYPO3\CMS\Core\Tests\AccessibleObjectInterface;

class ServiceFactoryTest extends AbstractUnitTest
{

    /**
     * Checks if the static getter of the service factory class returns a
     * correct instance.
     *
     * @test
     */
    public function classGetterWorks()
    {
        $serviceFactory = ServiceFactory::getInstance();
        $this->assertInstanceOf(ServiceFactory::class, $serviceFactory);

        unset($serviceFactory);
    }

    /**
     * Checks if a service can be manually added to a service factory instance.
     *
     * Will also check if the function `hasService()` runs correctly.
     *
     * @test
     */
    public function serviceCanBeAdded()
    {
        /** @var ServiceFactory|AccessibleObjectInterface $serviceFactory */
        $serviceFactory = $this->getAccessibleMock(ServiceFactory::class, ['dummy']);

        $this->assertFalse($serviceFactory->has(ParentsService::class));

        $serviceFactory->attach(ParentsService::class, []);

        $this->assertTrue($serviceFactory->has(ParentsService::class));

        unset($serviceFactory);
    }

    /**
     * Checks if trying to add the same service twice throws an exception.
     *
     * @test
     */
    public function checkDuplicateServiceEntryWhileAddingService()
    {
        $this->expectException(DuplicateEntryException::class);

        $serviceFactory = ServiceFactory::getInstance();
        $serviceFactory->attach(ParentsService::class, [])
            ->attach(ParentsService::class, []);

        unset($serviceFactory);
    }

    /**
     * Will check if trying to get a service instance from the service factory
     * before it has been initialized throws an exception.
     *
     * @test
     */
    public function getServiceInstanceBeforeInitializationThrowsException()
    {
        $this->expectException(InitializationNotSetException::class);

        $serviceFactory = ServiceFactory::getInstance();
        $serviceFactory->attach(ParentsService::class, [])
            ->get(ParentsService::class);

        unset($serviceFactory);
    }

    /**
     * Will check that trying to set the current manipulated service on a
     * service which was not added to the service factory will throw an
     * exception.
     *
     * @test
     */
    public function forServiceNotAddedThrowsException()
    {
        $this->expectException(Exception::class);

        $serviceFactory = ServiceFactory::getInstance();
        $serviceFactory->with(ParentsService::class);

        unset($serviceFactory);
    }

    /**
     * When trying to add an option to a service which was not added to the
     * service factory, an exception should be thrown.
     *
     * @test
     */
    public function setOptionForNotAddedServiceThrowsException()
    {
        $this->expectException(InitializationNotSetException::class);

        $serviceFactory = ServiceFactory::getInstance();
        $serviceFactory->setOption(CacheService::OPTION_CACHE_OPTIONS, ['foo' => 'bar']);
    }

    /**
     * Will check if setting an option for a service works correctly.
     *
     * @test
     */
    public function setOptionForServiceWorks()
    {
        $serviceFactory = ServiceFactory::getInstance();
        $serviceFactory->attach(CacheService::class)
            ->setOption(CacheService::OPTION_CACHE_OPTIONS, ['foo' => 'bar']);

        $options = $serviceFactory->with(CacheService::class)
            ->getOption(CacheService::OPTION_CACHE_OPTIONS);

        $this->assertEquals(
            $options,
            ['foo' => 'bar']
        );

        unset($serviceFactory);
    }

    /**
     * Will check if an exception is thrown when the service factory is
     * initialized with a wrong service.
     *
     * @test
     */
    public function initializeWithWrongServiceThrowsException()
    {
        $this->expectException(WrongInheritanceException::class);

        $serviceFactory = ServiceFactory::getInstance();
        $serviceFactory->attach('WrongClassName')
            ->initialize();

        unset($serviceFactory);
    }

    /**
     * Will test if a service which was added to the service factory can be
     * retrieved with the "getter" after the service factory was initialized.
     *
     * @test
     */
    public function getServiceWorks()
    {
        $serviceFactory = ServiceFactory::getInstance();

        $serviceFactory->attach(ParentsService::class)
            ->initialize();

        $parentServiceInstance = $serviceFactory->get(ParentsService::class);

        $this->assertEquals(ParentsService::class, get_class($parentServiceInstance));

        unset($serviceFactory);
    }

    /**
     * Will test if, after the service factory was initialized, trying to access
     * a not existing service throws an exception.
     *
     * @test
     */
    public function getNotExistingServiceThrowsException()
    {
        $serviceFactory = ServiceFactory::getInstance();

        $serviceFactory->attach(ParentsService::class)
            ->initialize();

        $this->expectException(EntryNotFoundException::class);
        $serviceFactory->get(\stdClass::class);

        unset($serviceFactory);
    }

    /**
     * In an initialized service factory, trying to run a wrong service should
     * throw an exception.
     *
     * @test
     */
    public function runWrongServiceThrowsException()
    {
        $this->expectException(WrongInheritanceException::class);

        $serviceFactory = ServiceFactory::getInstance();
        $serviceFactory->initialize();

        $dto = new GetConfigurationObjectDTO(DummyConfigurationObject::class, $serviceFactory);
        $serviceFactory->runServicesFromEvent(\stdClass::class, 'dummyMethod', $dto);

        unset($serviceFactory);
    }

    /**
     * In an initialized service factory, trying to run a not existing method in
     * a service should throw an exception.
     *
     * @test
     */
    public function runWrongMethodFromServiceThrowsException()
    {
        $this->expectException(Exception::class);

        $serviceFactory = ServiceFactory::getInstance();

        $serviceFactory->attach(DummyService::class)
            ->initialize();

        $dto = new GetConfigurationObjectDTO(DummyConfigurationObject::class, $serviceFactory);

        $serviceFactory->runServicesFromEvent(DummyService::class, 'wrongMethodName', $dto);

        unset($serviceFactory);
    }

    /**
     * Will test if the event from a service runs. We use a dummy service, which
     * contains one event which changes a property of its own: if the event runs
     * correctly, we should be able to fetch the new value of the property.
     *
     * @test
     */
    public function dummyServiceEventRunsCorrectly()
    {
        $serviceFactory = ServiceFactory::getInstance();
        $dto = new GetConfigurationObjectDTO(DummyConfigurationObject::class, $serviceFactory);

        $serviceFactory->attach(DummyService::class)
            ->initialize();

        $this->assertEquals(null, DummyService::getFoo());

        // This event will change the value of the `foo` property.
        $serviceFactory->runServicesFromEvent(DummyService::class, 'configurationObjectBefore', $dto);

        $this->assertEquals(DummyService::FOO_VALUE, DummyService::getFoo());

        unset($serviceFactory);
    }

    /**
     * Will check if two service factories with same arguments still have
     * different hashes.
     *
     * @test
     */
    public function checkServiceFactoryHashIsDifferent()
    {
        $serviceFactory1 = ServiceFactory::getInstance();
        $serviceFactory2 = ServiceFactory::getInstance();

        $this->assertNotEquals($serviceFactory1->getHash(), $serviceFactory2->getHash());

        unset($serviceFactory1);
        unset($serviceFactory2);
    }
}
