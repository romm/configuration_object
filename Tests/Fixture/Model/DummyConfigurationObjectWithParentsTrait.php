<?php
namespace Romm\ConfigurationObject\Tests\Fixture\Model;

use Romm\ConfigurationObject\ConfigurationObjectInterface;
use Romm\ConfigurationObject\Service\Items\Parents\ParentsTrait;
use Romm\ConfigurationObject\Traits\ConfigurationObject\DefaultConfigurationObjectTrait;
use Romm\ConfigurationObject\Traits\ConfigurationObject\MagicMethodsTrait;

/**
 * @method setSubObject(DummyConfigurationObjectWithParentsTrait $subObject)
 * @method DummyConfigurationObjectWithParentsTrait getSubObject()
 * @method setSubObjects(DummyConfigurationObjectWithParentsTrait[] $subObject)
 * @method DummyConfigurationObjectWithParentsTrait[] getSubObjects()
 */
class DummyConfigurationObjectWithParentsTrait implements ConfigurationObjectInterface
{

    use DefaultConfigurationObjectTrait;
    use MagicMethodsTrait;
    use ParentsTrait;

    /**
     * @var \Romm\ConfigurationObject\Tests\Fixture\Model\DummyConfigurationObjectWithParentsTrait
     */
    protected $subObject;

    /**
     * @var \ArrayObject<\Romm\ConfigurationObject\Tests\Fixture\Model\DummyConfigurationObjectWithParentsTrait>
     */
    protected $subObjects;
}
