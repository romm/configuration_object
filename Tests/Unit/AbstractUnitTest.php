<?php
namespace Romm\ConfigurationObject\Tests\Unit;

use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

abstract class AbstractUnitTest extends UnitTestCase
{
    use ConfigurationObjectUnitTestUtility;

    protected $resetSingletonInstances = true;

    protected function setUp()
    {
        $this->setUpConfigurationObjectCore();
    }
}
