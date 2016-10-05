<?php
namespace Romm\ConfigurationObject\Tests\Unit\Traits\ConfigurationObject;

use Romm\ConfigurationObject\Service\ServiceFactory;
use Romm\ConfigurationObject\Tests\Fixture\Model\DummyConfigurationObject;
use Romm\ConfigurationObject\Tests\Unit\AbstractUnitTest;

class DefaultConfigurationObjectTraitTest extends AbstractUnitTest
{

    /**
     * Will test the default `ServiceFactory` instance returned by the default
     * configuration object trait.
     *
     * @test
     */
    public function checkDefaultConfigurationObjectServiceFactory()
    {
        $serviceFactory = DummyConfigurationObject::getConfigurationObjectServices();

        // Checking the returned instance is a `ServiceFactory` instance.
        $this->assertEquals(ServiceFactory::class, get_class($serviceFactory));

        // The default service factory must be a basic instance.
        $this->assertEquals(serialize(ServiceFactory::getInstance()), serialize($serviceFactory));

        unset($serviceFactory);
    }

}
