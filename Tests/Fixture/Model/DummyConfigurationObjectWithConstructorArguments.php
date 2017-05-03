<?php
namespace Romm\ConfigurationObject\Tests\Fixture\Model;

use Romm\ConfigurationObject\ConfigurationObjectInterface;
use Romm\ConfigurationObject\Traits\ConfigurationObject\DefaultConfigurationObjectTrait;

class DummyConfigurationObjectWithConstructorArguments implements ConfigurationObjectInterface
{
    use DefaultConfigurationObjectTrait;

    /**
     * @var string
     */
    protected $foo;

    /**
     * @var string
     */
    protected $bar;

    /**
     * @param string $foo
     * @param string $bar
     */
    public function __construct($foo, $bar)
    {
        $this->foo = $foo;
        $this->bar = $bar;
    }
}
