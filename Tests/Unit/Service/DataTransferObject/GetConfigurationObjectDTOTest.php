<?php
namespace Romm\ConfigurationObject\Tests\Unit\Service\DataTransferObject;

use Romm\ConfigurationObject\ConfigurationObjectInstance;
use Romm\ConfigurationObject\Service\DataTransferObject\GetConfigurationObjectDTO;
use Romm\ConfigurationObject\Service\ServiceFactory;
use TYPO3\CMS\Core\Tests\UnitTestCase;
use TYPO3\CMS\Extbase\Error\Result;

class GetConfigurationObjectDTOTest extends UnitTestCase
{

    /**
     * @var GetConfigurationObjectDTO
     */
    protected $getConfigurationObjectDTO;

    protected function setUp()
    {
        $this->getConfigurationObjectDTO = new GetConfigurationObjectDTO(
            AbstractServiceDTOTest::CONFIGURATION_OBJECT_TEST_CLASS,
            ServiceFactory::getInstance()
        );
    }

    /**
     * @test
     */
    public function configurationObjectDataCanBeSet()
    {
        $configurationObjectData = ['foo' => 'bar'];
        $this->getConfigurationObjectDTO->setConfigurationObjectData($configurationObjectData);
        $this->assertEquals(
            $configurationObjectData,
            $this->getConfigurationObjectDTO->getConfigurationObjectData()
        );
    }

    /**
     * @test
     */
    public function resultCanBeSet()
    {

        /** @var ConfigurationObjectInstance $configurationObject */
        $configurationObject = $this->getMock(
            ConfigurationObjectInstance::class,
            [],
            [
                $this->getMock(AbstractServiceDTOTest::CONFIGURATION_OBJECT_TEST_CLASS),
                new Result
            ]
        );

        $this->getConfigurationObjectDTO->setResult($configurationObject);

        $this->assertEquals(
            $configurationObject,
            $this->getConfigurationObjectDTO->getResult()
        );
    }

}