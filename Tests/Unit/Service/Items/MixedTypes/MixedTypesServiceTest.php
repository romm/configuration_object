<?php
namespace Romm\ConfigurationObject\Tests\Unit\Service\Items\DataPreProcessor;

use Romm\ConfigurationObject\Service\Items\MixedTypes\MixedTypesService;
use Romm\ConfigurationObject\Tests\Fixture\Model\DummyConfigurationObjectWithMixedTypes;
use Romm\ConfigurationObject\Tests\Unit\AbstractUnitTest;

class MixedTypesServiceTest extends AbstractUnitTest
{

    /**
     * Will test if the function `getInstanceClassName()` is called on a class
     * which implements the interface `MixedTypesInterface`.
     *
     * @test
     */
    public function mixedTypesResolverFunctionIsCalled()
    {
        $mixedTypesService = new MixedTypesService();
        $mixedTypesService->initialize();

        $data = ['foo' => 'foo'];
        $mixedTypesResolver = $mixedTypesService->getMixedTypesResolver($data, DummyConfigurationObjectWithMixedTypes::class);

        /*
         * The function
         * `DummyConfigurationObjectWithMixedTypes::getInstanceClassName()`
         * will set the object type to `\stdClass`.
         */
        $this->assertEquals(
            $mixedTypesResolver->getObjectType(),
            \stdClass::class
        );

        unset($mixedTypesService);
    }

    /**
     * If the service is called with a class which does not implement the
     * interface `MixedTypesInterface`, a default preprocessor must be used, and
     * it should always be the same instance, to prevent creating a new instance
     * at each call.
     *
     * @test
     */
    public function defaultProcessorIsReturnedOnClassWithoutInterface()
    {
        $mixedTypesService = new MixedTypesService();
        $mixedTypesService->initialize();

        $data = ['foo' => 'foo'];

        $mixedTypesResolver1 = $mixedTypesService->getMixedTypesResolver($data, \stdClass::class);
        $this->assertEquals($data, $mixedTypesResolver1->getData());

        $mixedTypesResolver2 = $mixedTypesService->getMixedTypesResolver($data, self::class);
        $this->assertEquals($data, $mixedTypesResolver2->getData());

        // The mixed-types resolver must be the same instance.
        $this->assertEquals(spl_object_hash($mixedTypesResolver1), spl_object_hash($mixedTypesResolver2));

        unset($mixedTypesService);
    }
}
