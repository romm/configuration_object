<?php
namespace Romm\ConfigurationObject\Tests\Unit\Service\Items\ConfigurationArray;

use Romm\ConfigurationObject\Service\DataTransferObject\ConfigurationObjectConversionDTO;
use Romm\ConfigurationObject\Service\Items\StoreConfigurationArray\StoreConfigurationArrayService;
use Romm\ConfigurationObject\Service\Items\StoreConfigurationArray\StoreConfigurationArrayTrait;
use Romm\ConfigurationObject\Service\ServiceFactory;
use Romm\ConfigurationObject\Tests\Fixture\Model\DummyConfigurationObject;
use Romm\ConfigurationObject\Tests\Fixture\Model\DummyConfigurationObjectWithConfigurationArrayTrait;
use TYPO3\CMS\Core\Tests\UnitTestCase;

class StoreConfigurationArrayServiceTest extends UnitTestCase
{

    /**
     * Will test if the `StoreConfigurationArrayService` stores the
     * configuration array with a configuration object which uses the trait
     * `StoreConfigurationArrayTrait`.
     *
     * @test
     * @dataProvider dataProviderForCheckConfigurationArrayIsInserted
     *
     * @param object $object
     * @param mixed  $source
     * @param array  $result
     */
    public function checkStoreConfigurationArrayIsInserted($object, $source, array $result)
    {
        $serviceFactory = ServiceFactory::getInstance();
        $serviceDataTransferObject = new ConfigurationObjectConversionDTO(get_class($object), $serviceFactory);
        $serviceDataTransferObject->setResult($object)
            ->setSource($source);

        $storeConfigurationArrayService = new StoreConfigurationArrayService();
        $storeConfigurationArrayService->objectConversionAfter($serviceDataTransferObject);

        /** @var StoreConfigurationArrayTrait $entity */
        $entity = $serviceDataTransferObject->getResult();

        $this->assertEquals($result, $entity->getConfigurationArray());
    }

    /**
     * Data provider for `checkConfigurationArrayIsInserted()`.
     *
     * @return array
     */
    public function dataProviderForCheckConfigurationArrayIsInserted()
    {
        $modelWithConfigurationArrayTrait = new DummyConfigurationObjectWithConfigurationArrayTrait();

        return [
            [
                $modelWithConfigurationArrayTrait,
                ['foo' => 'bar'],
                ['foo' => 'bar']
            ],
            [
                $modelWithConfigurationArrayTrait,
                'foo',
                ['foo']
            ]
        ];
    }

    /**
     * Will test if running the `StoreConfigurationArrayService` does not try to
     * store the configuration array for a configuration object which does not
     * use the trait `StoreConfigurationArrayTrait`.
     *
     * @test
     */
    public function checkStoreConfigurationArrayServiceDoesNothingIfConfigurationObjectDoesNotUseTrait()
    {
        $object = new DummyConfigurationObject();

        $serviceFactory = ServiceFactory::getInstance();
        $serviceDataTransferObject = new ConfigurationObjectConversionDTO(get_class($object), $serviceFactory);
        $serviceDataTransferObject->setResult($object);

        /** @var StoreConfigurationArrayService|\PHPUnit_Framework_MockObject_MockObject $storeConfigurationArrayService */
        $storeConfigurationArrayService = $this->getMock(StoreConfigurationArrayService::class, ['storeConfigurationArray']);

        /*
         * The method `storeConfigurationArray()` should never be called because
         * the configuration object does not use the trait
         * `StoreConfigurationArrayTrait`.
         */
        $storeConfigurationArrayService->expects($this->never())
            ->method('storeConfigurationArray');

        $storeConfigurationArrayService->objectConversionAfter($serviceDataTransferObject);
    }
}
