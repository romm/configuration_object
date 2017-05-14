<?php
namespace Romm\ConfigurationObject\Tests\Unit\Core\Service\Cache;

use Romm\ConfigurationObject\Core\Service\ObjectService;
use Romm\ConfigurationObject\Tests\Fixture\Exception\DummyException;
use Romm\ConfigurationObject\Tests\Fixture\Model\ModelWithSilentExceptions;
use Romm\ConfigurationObject\Tests\Unit\AbstractUnitTest;

class ObjectServiceTest extends AbstractUnitTest
{
    /**
     * @test
     */
    public function silentExceptionIsCatch()
    {
        $service = new ObjectService;
        $model = new ModelWithSilentExceptions;

        $service->getObjectProperty($model, 'foo');
    }

    /**
     * @test
     */
    public function notSilentExceptionIsThrown()
    {
        $this->setExpectedException(DummyException::class);

        $service = new ObjectService;
        $model = new ModelWithSilentExceptions;

        $service->getObjectProperty($model, 'bar');
    }
}
