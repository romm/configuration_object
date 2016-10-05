<?php
namespace Romm\ConfigurationObject\Tests\Unit\Traits\ConfigurationObject;

use Romm\ConfigurationObject\Tests\Fixture\Model\DummyConfigurationObject;
use Romm\ConfigurationObject\Tests\Fixture\Model\DummyObjectWithFooAttribute;
use Romm\ConfigurationObject\Tests\Unit\AbstractUnitTest;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class ArrayConversionTraitTest extends AbstractUnitTest
{

    /**
     * Will test if a configuration object can be converted into an array.
     *
     * @test
     */
    public function checkArrayConversion()
    {
        $foo = 'foo';
        $bar = [
            'bar1' => 'bar',
            'bar2' => 'bar'
        ];

        $dummyConfigurationObject = new DummyConfigurationObject();
        $dummyConfigurationObject->setFoo($foo);
        $dummyConfigurationObject->setBar($bar);

        $expected = [
            'foo'       => $foo,
            'bar'       => $bar,
            'subObject' => null
        ];

        $this->assertEquals($expected, $dummyConfigurationObject->toArray());

        unset($dummyConfigurationObject);
    }

    /**
     * Will test if a configuration object with two depth levels can be
     * converted into an array.
     *
     * @test
     */
    public function checkDeepArrayConversion()
    {
        $foo = 'foo';
        $simpleModel = new DummyObjectWithFooAttribute();
        $simpleModel->setFoo('foo');
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($simpleModel);
        $bar = [
            'bar1'          => 'bar',
            'bar2'          => 'bar',
            'objectStorage' => $objectStorage,
            'simpleModel'   => $simpleModel
        ];

        $dummyConfigurationObject = new DummyConfigurationObject();
        $dummyConfigurationObject->setFoo($foo);
        $dummyConfigurationObject->setBar($bar);

        $dummyConfigurationObjectSub = clone $dummyConfigurationObject;

        $dummyConfigurationObject->setSubObject($dummyConfigurationObjectSub);

        $expectedBar = [
            'bar1'          => 'bar',
            'bar2'          => 'bar',
            'objectStorage' => [
                0 => ['foo' => 'foo']
            ],
            'simpleModel'   => ['foo' => 'foo']
        ];
        $expected = [
            'foo'       => $foo,
            'bar'       => $expectedBar,
            'subObject' => [
                'foo'       => $foo,
                'bar'       => $expectedBar,
                'subObject' => null
            ]
        ];

        $this->assertEquals($expected, $dummyConfigurationObject->toArray());

        unset($dummyConfigurationObject);
        unset($dummyConfigurationObjectSub);
    }
}
