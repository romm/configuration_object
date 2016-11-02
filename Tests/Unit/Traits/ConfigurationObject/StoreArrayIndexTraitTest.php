<?php
namespace Romm\ConfigurationObject\Tests\Unit\Traits\ConfigurationObject;

use Romm\ConfigurationObject\Tests\Unit\AbstractUnitTest;
use Romm\ConfigurationObject\Traits\ConfigurationObject\StoreArrayIndexTrait;

class StoreArrayIndexTraitTest extends AbstractUnitTest
{

    /**
     * Will test if the setter and getter work.
     *
     * @test
     */
    public function arrayIndexCanBeSet()
    {
        /** @var StoreArrayIndexTrait $mockedStoreArrayIndexTrait */
        $mockedStoreArrayIndexTrait = $this->getMockForTrait(StoreArrayIndexTrait::class);

        $mockedStoreArrayIndexTrait->setArrayIndex('foo');

        $this->assertEquals('foo', $mockedStoreArrayIndexTrait->getArrayIndex());
    }
}
