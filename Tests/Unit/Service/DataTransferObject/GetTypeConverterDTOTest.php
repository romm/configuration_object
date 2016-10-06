<?php
namespace Romm\ConfigurationObject\Tests\Unit\Service\DataTransferObject;

use Romm\ConfigurationObject\Service\DataTransferObject\GetTypeConverterDTO;
use Romm\ConfigurationObject\Service\ServiceFactory;
use Romm\ConfigurationObject\Tests\Unit\AbstractUnitTest;
use TYPO3\CMS\Extbase\Property\TypeConverterInterface;

class GetTypeConverterDTOTest extends AbstractUnitTest
{

    /**
     * @var GetTypeConverterDTO
     */
    protected $getTypeConverterDataTransferObject;

    protected function setUp()
    {
        parent::setUp();

        $this->getTypeConverterDataTransferObject = new GetTypeConverterDTO(
            AbstractServiceDTOTest::CONFIGURATION_OBJECT_TEST_CLASS,
            ServiceFactory::getInstance()
        );
    }

    /**
     * @test
     */
    public function typeConverterCanBeSet()
    {
        /** @var TypeConverterInterface $typeConverter */
        $typeConverter = $this->getMock(TypeConverterInterface::class);
        $this->getTypeConverterDataTransferObject->setTypeConverter($typeConverter);
        $this->assertEquals(
            $typeConverter,
            $this->getTypeConverterDataTransferObject->getTypeConverter()
        );
    }

    /**
     * @test
     */
    public function sourceCanBeSet()
    {
        $source = 'Hello world';
        $this->getTypeConverterDataTransferObject->setSource($source);
        $this->assertEquals(
            $source,
            $this->getTypeConverterDataTransferObject->getSource()
        );
    }

    /**
     * @test
     */
    public function targetTypeCanBeSet()
    {
        $targetType = 'int';
        $this->getTypeConverterDataTransferObject->setTargetType($targetType);
        $this->assertEquals(
            $targetType,
            $this->getTypeConverterDataTransferObject->getTargetType()
        );
    }

}
