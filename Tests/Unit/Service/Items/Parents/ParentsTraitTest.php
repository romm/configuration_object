<?php
namespace Romm\ConfigurationObject\Tests\Unit\Service\Items\Parents;

use Romm\ConfigurationObject\Exceptions\EntryNotFoundException;
use Romm\ConfigurationObject\Tests\Fixture\Model\DummyConfigurationObjectWithParentsTrait;
use TYPO3\CMS\Core\Tests\UnitTestCase;

class ParentsTraitTest extends UnitTestCase
{

    /**
     * @test
     */
    public function setParentSetsParent()
    {
        $object = new DummyConfigurationObjectWithParentsTrait();
        $stdClass = new \stdClass();

        $object->setParents([$stdClass]);

        $this->assertTrue($object->hasParent(\stdClass::class));
        $this->assertFalse($object->hasParent(self::class));
        $this->assertEquals(
            spl_object_hash($object->getFirstParent(\stdClass::class)),
            spl_object_hash($stdClass)
        );
    }

    /**
     * @test
     */
    public function withFirstParentWorks()
    {
        $object = new DummyConfigurationObjectWithParentsTrait();
        $stdClass = new \stdClass();

        $object->setParents([$stdClass]);
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

        $this->setExpectedException(EntryNotFoundException::class);
        $object->getFirstParent(\stdClass::class);

        unset($object);
    }
}
