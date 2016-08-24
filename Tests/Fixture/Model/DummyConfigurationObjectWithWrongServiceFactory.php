<?php
namespace Romm\ConfigurationObject\Tests\Fixture\Model;

use Romm\ConfigurationObject\ConfigurationObjectInterface;

class DummyConfigurationObjectWithWrongServiceFactory implements ConfigurationObjectInterface
{

    /**
     * @inheritdoc
     */
    public static function getConfigurationObjectServices()
    {
        return new \stdClass();
    }
}