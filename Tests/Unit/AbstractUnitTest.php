<?php
namespace Romm\ConfigurationObject\Tests\Unit;

use TYPO3\CMS\Core\Tests\UnitTestCase;

abstract class AbstractUnitTest extends UnitTestCase
{
    use ConfigurationObjectUnitTestUtility;

    protected function setUp()
    {
        $this->setUpConfigurationObjectCore();
    }
}
