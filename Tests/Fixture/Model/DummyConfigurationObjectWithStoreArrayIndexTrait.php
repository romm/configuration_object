<?php
namespace Romm\ConfigurationObject\Tests\Fixture\Model;

use Romm\ConfigurationObject\ConfigurationObjectInterface;
use Romm\ConfigurationObject\Traits\ConfigurationObject\DefaultConfigurationObjectTrait;
use Romm\ConfigurationObject\Traits\ConfigurationObject\StoreArrayIndexTrait;

class DummyConfigurationObjectWithStoreArrayIndexTrait implements ConfigurationObjectInterface
{

    use DefaultConfigurationObjectTrait;
    use StoreArrayIndexTrait;
}
