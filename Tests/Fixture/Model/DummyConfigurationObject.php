<?php
namespace Romm\ConfigurationObject\Tests\Fixture\Model;

use Romm\ConfigurationObject\ConfigurationObjectInterface;
use Romm\ConfigurationObject\Traits\ConfigurationObject\ArrayConversionTrait;
use Romm\ConfigurationObject\Traits\ConfigurationObject\DefaultConfigurationObjectTrait;
use Romm\ConfigurationObject\Traits\ConfigurationObject\MagicMethodsTrait;

/**
 * @method setFoo($foo)
 * @method string getFoo()
 * @method setBar(array $bar)
 * @method array getBar()
 * @method setSubObject(DummyConfigurationObject $subObject)
 * @method DummyConfigurationObject getSubObject()
 */
class DummyConfigurationObject implements ConfigurationObjectInterface
{

    use DefaultConfigurationObjectTrait;
    use MagicMethodsTrait;
    use ArrayConversionTrait;

    /**
     * @var string
     */
    protected $foo;

    /**
     * @var array
     */
    protected $bar;

    /**
     * @var \Romm\ConfigurationObject\Tests\Fixture\Model\DummyConfigurationObject
     */
    protected $subObject;
}
