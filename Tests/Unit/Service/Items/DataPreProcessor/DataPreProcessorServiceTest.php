<?php
namespace Romm\ConfigurationObject\Tests\Unit\Service\Items\DataPreProcessor;

use Romm\ConfigurationObject\Service\Items\DataPreProcessor\DataPreProcessorService;
use Romm\ConfigurationObject\Tests\Fixture\Model\DummyConfigurationObjectWithDataPreProcessor;
use Romm\ConfigurationObject\Tests\Unit\AbstractUnitTest;

class DataPreProcessorServiceTest extends AbstractUnitTest
{

    /**
     * Will test if the function `dataPreProcessor()` is called on a class which
     * implements the interface `DataPreProcessorInterface`.
     *
     * @test
     */
    public function dataPreProcessorFunctionIsCalled()
    {
        $dataPreProcessorService = new DataPreProcessorService();
        $dataPreProcessorService->initialize();

        $data = ['foo' => 'foo'];
        $dataPreProcessor = $dataPreProcessorService->getDataPreProcessor($data, DummyConfigurationObjectWithDataPreProcessor::class);

        /*
         * The function
         * `DummyConfigurationObjectWithDataPreProcessor::dataPreProcessor()`
         * will add an entry ['bar' => 'bar'] to the data array, we can then
         * check it has been inserted correctly.
         */
        $this->assertEquals(
            $dataPreProcessor->getData(),
            [
                'foo' => 'foo',
                'bar' => 'bar'
            ]
        );

        unset($dataPreProcessorService);
    }

    /**
     * If the service is called with a class which does not implement the
     * interface `DataPreProcessorInterface`, a default preprocessor must be
     * used, and it should always be the same instance, to prevent creating a
     * new instance at each call.
     *
     * @test
     */
    public function defaultProcessorIsReturnedOnClassWithoutInterface()
    {
        $dataPreProcessorService = new DataPreProcessorService();
        $dataPreProcessorService->initialize();

        $data = ['foo' => 'foo'];

        $dataPreProcessor1 = $dataPreProcessorService->getDataPreProcessor($data, \stdClass::class);
        $this->assertEquals($data, $dataPreProcessor1->getData());

        $dataPreProcessor2 = $dataPreProcessorService->getDataPreProcessor($data, self::class);
        $this->assertEquals($data, $dataPreProcessor2->getData());

        // The data-processor must be the same instance.
        $this->assertEquals(spl_object_hash($dataPreProcessor1), spl_object_hash($dataPreProcessor2));

        unset($dataPreProcessorService);
    }
}
