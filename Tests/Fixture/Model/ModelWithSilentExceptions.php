<?php
namespace Romm\ConfigurationObject\Tests\Fixture\Model;

use Romm\ConfigurationObject\Tests\Fixture\Exception\DummyException;
use Romm\ConfigurationObject\Tests\Fixture\Exception\SilentException;

class ModelWithSilentExceptions
{
    /**
     * @var string
     */
    protected $foo;

    /**
     * @var array
     */
    protected $bar;

    /**
     * @throws SilentException
     */
    public function getFoo()
    {
        throw new SilentException;
    }

    /**
     * @throws DummyException
     */
    public function getBar()
    {
        throw new DummyException;
    }
}
