<?php
namespace Romm\ConfigurationObject\Tests\Unit\Service\Items\Parents;

use Romm\ConfigurationObject\ConfigurationObjectInstance;
use Romm\ConfigurationObject\Service\DataTransferObject\ConfigurationObjectConversionDTO;
use Romm\ConfigurationObject\Service\DataTransferObject\GetConfigurationObjectDTO;
use Romm\ConfigurationObject\Service\Items\Parents\ParentsService;
use Romm\ConfigurationObject\Service\ServiceFactory;
use Romm\ConfigurationObject\Tests\Fixture\Model\DummyConfigurationObjectWithParentsTrait;
use Romm\ConfigurationObject\Tests\Unit\AbstractUnitTest;
use TYPO3\CMS\Extbase\Error\Result;

class ParentsServiceTest extends AbstractUnitTest
{

    /**
     * Will go through the whole process to fill the parents of an object, using
     * the parents service.
     *
     * @test
     */
    public function fillObjectParentsWorks()
    {
        $serviceFactory = ServiceFactory::getInstance();
        $parentsService = new ParentsService();

        $object = new DummyConfigurationObjectWithParentsTrait();
        $subObject = new DummyConfigurationObjectWithParentsTrait();
        $subObject->setSubObjects([clone $object]);
        $object->setSubObject($subObject);

        $configurationObjectConversionDTO = new ConfigurationObjectConversionDTO(DummyConfigurationObjectWithParentsTrait::class, $serviceFactory);
        $configurationObjectConversionDTO->setResult($object);

        $parentsService->objectConversionAfter($configurationObjectConversionDTO);
        $parentsService->runDelayedCallbacks($configurationObjectConversionDTO);

        $getConfigurationObjectDTO = new GetConfigurationObjectDTO(DummyConfigurationObjectWithParentsTrait::class, $serviceFactory);
        $result = new Result();
        $objectInstance = new ConfigurationObjectInstance($object, $result);
        $getConfigurationObjectDTO->setResult($objectInstance);

        $parentsService->configurationObjectAfter($getConfigurationObjectDTO);
        $parentsService->runDelayedCallbacks($getConfigurationObjectDTO);

        $this->assertEquals(
            spl_object_hash($object),
            spl_object_hash($subObject->getFirstParent(DummyConfigurationObjectWithParentsTrait::class))
        );

        unset($result);
        unset($objectInstance);
        unset($configurationObjectConversionDTO);
        unset($getConfigurationObjectDTO);
        unset($serviceFactory);
        unset($subObject);
        unset($object);
    }
}
