<?php
namespace Romm\ConfigurationObject\Tests\Unit\Service\DataTransferObject;

use Romm\ConfigurationObject\Service\DataTransferObject\ConfigurationObjectConversionDTO;
use Romm\ConfigurationObject\Service\ServiceFactory;
use Romm\ConfigurationObject\Tests\Unit\AbstractUnitTest;

class ConfigurationObjectConversionDTOTest extends AbstractUnitTest
{

    /**
     * @var ConfigurationObjectConversionDTO
     */
    protected $configurationObjectConversionDataTransferObject;

    protected function setUp()
    {
        parent::setUp();

        $this->configurationObjectConversionDataTransferObject = new ConfigurationObjectConversionDTO(
            AbstractServiceDTOTest::CONFIGURATION_OBJECT_TEST_CLASS,
            ServiceFactory::getInstance()
        );
    }

    /**
     * @test
     */
    public function sourceCanBeSet()
    {
        $source = 'Hello world';
        $this->configurationObjectConversionDataTransferObject->setSource($source);
        $this->assertEquals(
            $source,
            $this->configurationObjectConversionDataTransferObject->getSource()
        );
    }

    /**
     * @test
     */
    public function targetTypeCanBeSet()
    {
        $targetType = 'int';
        $this->configurationObjectConversionDataTransferObject->setTargetType($targetType);
        $this->assertEquals(
            $targetType,
            $this->configurationObjectConversionDataTransferObject->getTargetType()
        );
    }

    /**
     * @test
     */
    public function convertedChildPropertiesCanBeSet()
    {
        $convertedChildProperties = ['foo' => 'bar'];
        $this->configurationObjectConversionDataTransferObject->setConvertedChildProperties($convertedChildProperties);
        $this->assertEquals(
            $convertedChildProperties,
            $this->configurationObjectConversionDataTransferObject->getConvertedChildProperties()
        );
    }

    /**
     * @test
     */
    public function currentPropertyPathCanBeSet()
    {
        $currentPropertyPath = ['foo' => 'bar'];
        $this->configurationObjectConversionDataTransferObject->setCurrentPropertyPath($currentPropertyPath);
        $this->assertEquals(
            $currentPropertyPath,
            $this->configurationObjectConversionDataTransferObject->getCurrentPropertyPath()
        );
    }

    /**
     * @test
     */
    public function resultCanBeSet()
    {
        $result = ['bar' => 'foo'];
        $this->configurationObjectConversionDataTransferObject->setResult($result);
        $this->assertEquals(
            $result,
            $this->configurationObjectConversionDataTransferObject->getResult()
        );
    }

}
