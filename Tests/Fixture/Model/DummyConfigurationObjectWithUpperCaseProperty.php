<?php
namespace Romm\ConfigurationObject\Tests\Fixture\Model;

/**
 * @method setUpperCaseProperty($foo)
 * @method string getUpperCaseProperty()
 */
class DummyConfigurationObjectWithUpperCaseProperty extends DummyConfigurationObject
{
    /**
     * @var string
     */
    protected $UpperCaseProperty;
}
