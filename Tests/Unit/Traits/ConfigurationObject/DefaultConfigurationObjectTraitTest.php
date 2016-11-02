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
        $this->assertInstanceOf(ServiceFactory::class, $serviceFactory);

        unset($serviceFactory);
    }
}
