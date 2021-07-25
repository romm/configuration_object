<?php
namespace Romm\ConfigurationObject\Tests\Unit\Service\Items\Parents;

use PHPUnit\Framework\MockObject\MockObject;
use Romm\ConfigurationObject\Service\Items\Parents\ParentsUtility;
use Romm\ConfigurationObject\Tests\Fixture\Model\DummyConfigurationObjectWithParentsTrait;
use Romm\ConfigurationObject\Tests\Unit\AbstractUnitTest;

class ParentsUtilityTest extends AbstractUnitTest
{

    /**
     * Will test if the method `checkClassUsesParentsTrait()` works correctly.
     *
     * @test
     */
    public function checkClassUsingParentsTraitAreRecognized()
    {
        $parentsUtility = new ParentsUtility();

        $this->assertFalse($parentsUtility->classUsesParentsTrait(\stdClass::class));
        $this->assertTrue($parentsUtility->classUsesParentsTrait(DummyConfigurationObjectWithParentsTrait::class));

        $stdClass = new \stdClass();
        $this->assertFalse($parentsUtility->classUsesParentsTrait($stdClass));
        unset($stdClass);

        $dummyConfigurationObject = new DummyConfigurationObjectWithParentsTrait();
        $this->assertTrue($parentsUtility->classUsesParentsTrait($dummyConfigurationObject));
        unset($dummyConfigurationObject);

        $mockedDummyConfigurationObjectWithParentsTrait = $this->getMockBuilder(DummyConfigurationObjectWithParentsTrait::class)
            ->getMock();
        $this->assertTrue($parentsUtility->classUsesParentsTrait($mockedDummyConfigurationObjectWithParentsTrait));
        unset($mockedDummyConfigurationObjectWithParentsTrait);

        unset($parentsUtility);
    }

    /**
     * Will check that the list of classes using the trait `ParentsTrait` are
     * stored in local cache to improve performances.
     *
     * @test
     */
    public function checkClassesUsingParentsTraitAreStoredInLocalCache()
    {
        /** @var ParentsUtility|MockObject $parentsUtility */
        $parentsUtility = $this->getMockBuilder(ParentsUtility::class)
            ->setMethods(['checkClassUsesParentsTrait'])
            ->getMock();
        $parentsUtility->expects($this->once())
            ->method('checkClassUsesParentsTrait')
            ->will($this->returnValue(false));

        for ($i = 0; $i < 10; $i++) {
            $parentsUtility->classUsesParentsTrait(\stdClass::class);
        }
    }
}
