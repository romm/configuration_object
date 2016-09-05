<?php
namespace Romm\ConfigurationObject\Tests\Unit\Traits\ConfigurationObject;

use Romm\ConfigurationObject\Exceptions\MethodNotFoundException;
use Romm\ConfigurationObject\Tests\Fixture\Model\DummyConfigurationObject;
use TYPO3\CMS\Core\Tests\UnitTestCase;

class MagicMethodsTraitTest extends UnitTestCase
{

    /**
     * Checks that the magic getters and setters work properly for an object
     * which uses the trait `MagicMethodTrait`: methods do not need to be
     * implemented and should work thanks to the `__call` method.
     *
     * @test
     */
    public function checkMagicGettersAndSetters()
    {
        $foo = 'foo';
        $bar = [
            'bar1' => 'bar',
            'bar2' => 'bar'
        ];

        $dummyConfigurationObject = new DummyConfigurationObject();
        $dummyConfigurationObject->setFoo($foo);
        $dummyConfigurationObject->setBar($bar);

        $this->assertEquals($foo, $dummyConfigurationObject->getFoo());
        $this->assertEquals($bar, $dummyConfigurationObject->getBar());

        // Trying to call a not existing getter should throw an exception.
        $this->setExpectedException(MethodNotFoundException::class);
        $this->assertEquals(null, call_user_func([$dummyConfigurationObject, 'getNotExistingProperty']));

        unset($dummyConfigurationObject);
    }
}
