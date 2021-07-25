<?php
namespace Romm\ConfigurationObject\Tests\Unit\Service;

use Romm\ConfigurationObject\Exceptions\InvalidServiceOptionsException;
use Romm\ConfigurationObject\Exceptions\InvalidTypeException;
use Romm\ConfigurationObject\Service\AbstractService;
use Romm\ConfigurationObject\Service\DataTransferObject\GetConfigurationObjectDTO;
use Romm\ConfigurationObject\Service\ServiceFactory;
use Romm\ConfigurationObject\Tests\Unit\AbstractUnitTest;
use Romm\ConfigurationObject\Tests\Unit\Service\DataTransferObject\AbstractServiceDTOTest;
use TYPO3\CMS\Core\Tests\AccessibleObjectInterface;

class AbstractServiceTest extends AbstractUnitTest
{

    /**
     * @var AbstractService|AccessibleObjectInterface
     */
    protected $abstractService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->abstractService = $this->getAccessibleMock(AbstractService::class, ['dummy']);
    }

    /**
     * Will test if the creation of a service initializes correctly the options.
     * Three major behaviours should occur:
     *
     * - If an unsupported option is given, an exception must be thrown;
     * - If a required option is not given, an exception must be thrown;
     * - The supported options must respect an array syntax, if they do not, an
     *   exception must be thrown;
     * - The given options must be filled in the service property.
     *
     * @test
     * @dataProvider initializationDataProvider
     *
     * @param array       $supportedOptions List of options supported by the service.
     * @param array       $options          Arbitrary list of options given to the service.
     * @param string|null $exceptionThrown  If filled, this exception should be thrown at the service initialization.
     * @param array       $resultOptions    If filled, then after the service initialization the options should be filled with this value.
     */
    public function checkOptionsAreInitializedCorrectlyInConstructor(array $supportedOptions, array $options, $exceptionThrown = null, array $resultOptions = [])
    {
        $this->inject($this->abstractService, 'supportedOptions', $supportedOptions);

        if (null !== $exceptionThrown) {
            $this->expectException($exceptionThrown);
        }

        $this->abstractService->initializeObject($options);

        if (false === empty($resultOptions)) {
            $this->assertEquals($resultOptions, $this->abstractService->_get('options'));
        }
    }

    /**
     * Data provider for `checkOptionsAreInitializedCorrectlyInConstructor()`.
     *
     * @return array
     */
    public function initializationDataProvider()
    {
        return [
            // Required option with default value not given.
            [
                'supportedOptions' => ['myOption' => ['foo', true]],
                'options'          => [],
                'exceptionThrown'  => null
            ],
            // Required option with default value given.
            [
                'supportedOptions' => ['myOption' => ['foo', true]],
                'options'          => ['myOption' => 'bar'],
                'exceptionThrown'  => null,
                'expectedResult'   => ['myOption' => 'bar']
            ],
            // Required option with no default value not given: exception expected.
            [
                'supportedOptions' => ['myOption' => [null, true]],
                'options'          => [],
                'exceptionThrown'  => InvalidServiceOptionsException::class
            ],
            // Required option with no default value given.
            [
                'supportedOptions' => ['myOption' => [null, true]],
                'options'          => ['myOption' => 'bar'],
                'exceptionThrown'  => null,
                'expectedResult'   => ['myOption' => 'bar']
            ],
            // Not Required option given.
            [
                'supportedOptions' => ['myOption' => [null, false]],
                'options'          => ['myOption' => 'bar'],
                'exceptionThrown'  => null,
                'expectedResult'   => ['myOption' => 'bar']
            ],
            // Not Required option not given.
            [
                'supportedOptions' => ['myOption' => [null, false]],
                'options'          => [],
                'exceptionThrown'  => null,
                'expectedResult'   => ['myOption' => null]
            ],
            // Not existing option given: exception expected.
            [
                'supportedOptions' => [],
                'options'          => ['myNotExistingOption' => 'bar'],
                'exceptionThrown'  => InvalidServiceOptionsException::class
            ],
            // Supported options mapped incorrectly: exception expected.
            [
                'supportedOptions' => ['myOption' => 'foo'],
                'options'          => [],
                'exceptionThrown'  => InvalidTypeException::class
            ]
        ];
    }

    /**
     * Will test if the callbacks are correctly initialized when using the
     * function `delay()`.
     *
     * @test
     * @dataProvider callBackCanBeDelayedDataProvider
     *
     * @param array $delayedCallBacks
     * @param array $expectedResult
     * @param null  $exceptionThrown
     */
    public function callBackCanBeDelayed(array $delayedCallBacks, array $expectedResult, $exceptionThrown = null)
    {
        if (null !== $exceptionThrown) {
            $this->expectException($exceptionThrown);
        }

        $this->abstractService->_setStatic('delayedCallbacks', []);
        foreach ($delayedCallBacks as $callBackInfo) {
            /** @noinspection PhpMethodParametersCountMismatchInspection */
            $this->abstractService->_call('delay', $callBackInfo[0], $callBackInfo[1]);
        }

        $this->assertEquals($this->abstractService->_getStatic('delayedCallbacks'), $expectedResult);
    }

    /**
     * Data provider for `callBackCanBeDelayed()`.
     *
     * @return array
     */
    public function callBackCanBeDelayedDataProvider()
    {
        $callBackFoo = function () {
            echo 'foo';
        };
        $callBackBar = function () {
            echo 'bar';
        };

        return [
            // Testing exception thrown when bad priority type is given.
            [
                [
                    ['foo', $callBackFoo]
                ],
                [],
                InvalidTypeException::class
            ],
            // One delayed callback given.
            [
                [[100, $callBackFoo]],
                [100 => [$callBackFoo]]
            ],
            // Two delayed callbacks with same priority given.
            [
                [
                    [100, $callBackFoo],
                    [100, $callBackBar]
                ],
                [100 => [$callBackFoo, $callBackBar]]
            ],
            // Two delayed callbacks with different priority given.
            [
                [
                    [100, $callBackFoo],
                    [200, $callBackBar]
                ],
                [
                    100 => [$callBackFoo],
                    200 => [$callBackBar]
                ]
            ],
            // Nothing given.
            [
                [],
                []
            ]
        ];
    }

    /**
     * In this test, we delay a single callback which will, when it runs, modify
     * the data of a DTO. We then check if the data of the DTO was properly
     * updated after the delayed callbacks did run.
     *
     * @test
     */
    public function singleCallbackIsCalled()
    {
        $callBack = function (GetConfigurationObjectDTO $dto) {
            $dto->setConfigurationObjectData(['foo' => 'bar']);
        };
        $dto = new GetConfigurationObjectDTO(
            AbstractServiceDTOTest::CONFIGURATION_OBJECT_TEST_CLASS,
            ServiceFactory::getInstance()
        );

        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $this->abstractService->_call('delay', 100, $callBack);
        $this->abstractService->runDelayedCallbacks($dto);

        $this->assertEquals($dto->getConfigurationObjectData(), ['foo' => 'bar']);
    }

    /**
     * In this function, we delay two callbacks with two different priorities,
     * and we begin by registering the one with the highest priority. The two
     * callbacks will update the data of the DTO with different values.
     *
     * Then, we run the delayed callbacks, and we check if the data of the DTO
     * is the same as the data updated in the lowest priority callback (because
     * it should be the last one to run).
     *
     * @test
     */
    public function severalCallbacksAreCalledInOrder()
    {
        $callBackPriority100 = function (GetConfigurationObjectDTO $dto) {
            $dto->setConfigurationObjectData(['foo' => 'bar']);
        };
        $callBackPriority200 = function (GetConfigurationObjectDTO $dto) {
            $dto->setConfigurationObjectData(['bar' => 'foo']);
        };
        $dto = new GetConfigurationObjectDTO(
            AbstractServiceDTOTest::CONFIGURATION_OBJECT_TEST_CLASS,
            ServiceFactory::getInstance()
        );

        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $this->abstractService->_call('delay', 200, $callBackPriority200);
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $this->abstractService->_call('delay', 100, $callBackPriority100);
        $this->abstractService->runDelayedCallbacks($dto);

        $this->assertEquals($dto->getConfigurationObjectData(), ['foo' => 'bar']);
    }
}
