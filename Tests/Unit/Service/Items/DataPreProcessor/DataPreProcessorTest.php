<?php
namespace Romm\ConfigurationObject\Tests\Unit\Service\Items\DataPreProcessor;

use Romm\ConfigurationObject\Service\Items\DataPreProcessor\DataPreProcessor;
use Romm\ConfigurationObject\Tests\Unit\AbstractUnitTest;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Error\Result;

class DataPreProcessorTest extends AbstractUnitTest
{

    /**
     * A new instance of `DataPreProcessor` must create a new instance of
     * `Result`, which can then be used to add errors.
     *
     * @test
     */
    public function constructorCreatesResultInstance()
    {
        $dataPreProcessor = new DataPreProcessor();

        $this->assertEquals(
            Result::class,
            get_class($dataPreProcessor->getResult())
        );

        unset($dataPreProcessor);
    }

    /**
     * Will check that the function `addError()` works correctly.
     *
     * @test
     */
    public function addErrorAddsAnError()
    {
        $dataPreProcessor = new DataPreProcessor();

        $this->assertFalse($dataPreProcessor->getResult()->hasErrors());

        $errorName = 'hello world!';
        $error = new Error($errorName, 1337);
        $dataPreProcessor->addError($error);

        $this->assertTrue($dataPreProcessor->getResult()->hasErrors());
        $this->assertEquals(
            $errorName,
            $dataPreProcessor->getResult()->getFirstError()->getMessage()
        );

        unset($dataPreProcessor);
    }

    /**
     * @test
     */
    public function setDataSetsData()
    {
        $dataPreProcessor = new DataPreProcessor();
        $data = ['foo' => 'bar'];

        $dataPreProcessor->setData($data);
        $this->assertEquals($data, $dataPreProcessor->getData());

        unset($dataPreProcessor);
    }
}
