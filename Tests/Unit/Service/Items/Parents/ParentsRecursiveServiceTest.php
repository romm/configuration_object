<?php

namespace Romm\ConfigurationObject\Tests\Unit\Service\Items\Parents;

use Romm\ConfigurationObject\Service\Items\Parents\ParentsRecursiveService;
use Romm\ConfigurationObject\Service\Items\Parents\ParentsTrait;
use Romm\ConfigurationObject\Tests\Unit\AbstractUnitTest;

class ParentsRecursiveServiceTest extends AbstractUnitTest
{
    /**
     * @test
     */
    public function alongParentsGoesAlongAllParents()
    {
        $foundParents = [];

        /** @var ParentsTrait $foo */
        $foo = $this->getMockBuilder(ParentsTrait::class)
            ->setMockClassName('foo')
            ->getMockForTrait();

        /** @var ParentsTrait $bar */
        $bar = $this->getMockBuilder(ParentsTrait::class)
            ->setMockClassName('bar')
            ->getMockForTrait();

        /** @var ParentsTrait $baz */
        $baz = $this->getMockBuilder(ParentsTrait::class)
            ->setMockClassName('baz')
            ->getMockForTrait();

        $stdClass1 = new \stdClass();
        $stdClass2 = new \stdClass();

        $bar->attachParent($stdClass1);
        $baz->attachParent($stdClass2);

        $service = new ParentsRecursiveService();

        $service->alongParents(
            function ($parent) use (&$foundParents) {
                $foundParents[] = $parent;
            },
            $foo,
            [$foo, $bar, $baz]
        );

        $this->assertSame([$bar, $stdClass1, $baz, $stdClass2], $foundParents);
    }

    /**
     * @test
     */
    public function callbackReturnsFalse()
    {
        $foundParents = [];

        /** @var ParentsTrait $foo */
        $foo = $this->getMockBuilder(ParentsTrait::class)
            ->setMockClassName('foo')
            ->getMockForTrait();

        /** @var ParentsTrait $bar */
        $bar = $this->getMockBuilder(ParentsTrait::class)
            ->setMockClassName('bar')
            ->getMockForTrait();

        /** @var ParentsTrait $baz */
        $baz = $this->getMockBuilder(ParentsTrait::class)
            ->setMockClassName('baz')
            ->getMockForTrait();

        $service = new ParentsRecursiveService();

        $service->alongParents(
            function ($parent) use (&$foundParents, $bar) {
                $foundParents[] = $parent;

                return !$parent === $bar;
            },
            $foo,
            [$bar, $baz]
        );

        $this->assertSame([$bar], $foundParents);
    }
}
