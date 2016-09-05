<?php
namespace Romm\ConfigurationObject\Tests\Fixture\Model;

use Romm\ConfigurationObject\ConfigurationObjectInterface;
use Romm\ConfigurationObject\Traits\ConfigurationObject\DefaultConfigurationObjectTrait;
use Romm\ConfigurationObject\Traits\ConfigurationObject\MagicMethodsTrait;

/**
 * @method setFoo($foo)
 * @method string getFoo()
 */
class DummyConfigurationObjectWithAttributeContainingError implements ConfigurationObjectInterface
{

    use DefaultConfigurationObjectTrait;
    use MagicMethodsTrait;

    /**
     * @var string
     * @validate Romm\ConfigurationObject\Tests\Fixture\Validator\WrongValueValidator
     */
    protected $foo;
}
