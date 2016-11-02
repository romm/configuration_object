<?php
namespace Romm\ConfigurationObject\Tests\Fixture\Model;

use Romm\ConfigurationObject\ConfigurationObjectInterface;
use Romm\ConfigurationObject\Service\Items\StoreConfigurationArray\StoreConfigurationArrayTrait;
use Romm\ConfigurationObject\Traits\ConfigurationObject\DefaultConfigurationObjectTrait;

class DummyConfigurationObjectWithConfigurationArrayTrait implements ConfigurationObjectInterface
{
    use DefaultConfigurationObjectTrait;
    use StoreConfigurationArrayTrait;

    /**
     * @var string
     */
    protected $property;
}
