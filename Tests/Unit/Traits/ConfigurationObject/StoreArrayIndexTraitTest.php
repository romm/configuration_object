<?php
namespace Romm\ConfigurationObject\Tests\Unit\Traits\ConfigurationObject;

use Romm\ConfigurationObject\Traits\ConfigurationObject\StoreArrayIndexTrait;
use TYPO3\CMS\Core\Tests\UnitTestCase;

class StoreArrayIndexTraitTest extends UnitTestCase
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
