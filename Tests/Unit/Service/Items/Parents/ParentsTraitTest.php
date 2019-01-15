<?php
namespace Romm\ConfigurationObject\Tests\Unit\Service\Items\Parents;

use Romm\ConfigurationObject\Exceptions\DuplicateEntryException;
use Romm\ConfigurationObject\Exceptions\EntryNotFoundException;
use Romm\ConfigurationObject\Exceptions\InvalidTypeException;
use Romm\ConfigurationObject\Tests\Fixture\Model\DummyConfigurationObjectWithParentsTrait;
use Romm\ConfigurationObject\Tests\Fixture\Model\DummyInterface;
use Romm\ConfigurationObject\Tests\Unit\AbstractUnitTest;

class ParentsTraitTest extends AbstractUnitTest
{

    // @todo test
//    /**
//     * @test
//     */
//    public function setParentSetsParent()
//    {
//        $object = new DummyConfigurationObjectWithParentsTrait();
//        $stdClass = new \stdClass();
//
//        $object->setParents([$stdClass]);
//
//        $this->assertTrue($object->hasParent(\stdClass::class));
//        $this->assertFalse($object->hasParent(self::class));
//        $this->assertSame($stdClass, $object->getFirstParent(\stdClass::class));
//    }

    /**
     * @test
     */
    public function attachParentAttachesParent()
    {
        $object = new DummyConfigurationObjectWithParentsTrait();
        $stdClass = new \stdClass();

        $object->attachParent($stdClass);
        $this->assertTrue($object->hasParent(\stdClass::class));
        $this->assertSame($stdClass, $object->getFirstParent(\stdClass::class));
    }

    /**
     * @test
     */
    public function attachNonObjectThrowsException()
    {
        $this->expectException(InvalidTypeException::class);

        $object = new DummyConfigurationObjectWithParentsTrait();
        /** @noinspection PhpParamsInspection */
        $object->attachParent('foo');
    }

    /**
     * @test
     */
    public function attachExistingParentThrowsException()
    {
        $this->expectException(DuplicateEntryException::class);

        $object = new DummyConfigurationObjectWithParentsTrait();
        $stdClass = new \stdClass();

        $object->attachParent($stdClass);
        $object->attachParent($stdClass);
    }

    /**
     * @test
     */
    public function attachParentWithSameClassDoesNotThrowException()
    {
        $object = new DummyConfigurationObjectWithParentsTrait();
        $stdClass = new \stdClass();
        $stdClass2 = new \stdClass();

        $object->attachParent($stdClass);
        $object->attachParent($stdClass2);
    }

    /**
     * @test
     */
    public function attachParentsAttachesParents()
    {
        /** @var DummyConfigurationObjectWithParentsTrait|\PHPUnit_Framework_MockObject_MockObject $object */
        $object = $this->getMockBuilder(DummyConfigurationObjectWithParentsTrait::class)
            ->setMethods(['attachParent'])
            ->getMock();

        $foo = $this->prophesize('FooClass')->reveal();
        $bar = $this->prophesize('BarClass')->reveal();

        $object->expects($this->exactly(2))
            ->method('attachParent')
            ->withConsecutive([$foo, false], [$bar, false]);

        $object->attachParents([$foo, $bar]);
    }

    /**
     * @test
     */
    public function withFirstParentWorks()
    {
        $object = new DummyConfigurationObjectWithParentsTrait();
        $stdClass = new \stdClass();

        $object->attachParent($stdClass);
        $foo = 'foo';

        // The parent should be found, so the value of `$foo` should change.
        $object->withFirstParent(
            \stdClass::class,
            function () use (&$foo) {
                $foo = 'bar';
            }
        );

        $this->assertEquals('bar', $foo);

        /*
         * The parent should NOT be found, so the value of `$foo` should change
         * from the second callback.
         */
        $foo = 'foo';
        $object->withFirstParent(
            self::class,
            function () use (&$foo) {
                $foo = 'bar';
            },
            function () use (&$foo) {
                $foo = '42';
            }
        );

        $this->assertEquals('42', $foo);

        unset($object);
        unset($stdClass);
    }

    /**
     * @test
     */
    public function getNotExistingFirstParentThrowsException()
    {
        $object = new DummyConfigurationObjectWithParentsTrait();

        $this->expectException(EntryNotFoundException::class);
        $object->getFirstParent(\stdClass::class);

        unset($object);
    }

    /**
     * Checks that a parent with a given interface can be found.
     *
     * @test
     */
    public function hasParentWithInterface()
    {
        $dummy = $this->prophesize()->willImplement(DummyInterface::class)->reveal();

        $object = new DummyConfigurationObjectWithParentsTrait();
        $object->attachParent($dummy);

        $this->assertSame($dummy, $object->getFirstParent(DummyInterface::class));
    }
}
