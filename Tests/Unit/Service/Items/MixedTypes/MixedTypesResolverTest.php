<?php
namespace Romm\ConfigurationObject\Tests\Unit\Service\Items\MixedTypes;

use Romm\ConfigurationObject\Service\Items\MixedTypes\MixedTypesResolver;
use Romm\ConfigurationObject\Tests\Unit\AbstractUnitTest;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Error\Result;

class MixedTypesResolverTest extends AbstractUnitTest
{

    /**
     * A new instance of `MixedTypesResolver` must create a new instance of
     * `Result`, which can then be used to add errors.
     *
     * @test
     */
    public function constructorCreatesResultInstance()
    {
        $mixedTypeResolver = new MixedTypesResolver();

        $this->assertEquals(
            Result::class,
            get_class($mixedTypeResolver->getResult())
        );

        unset($mixedTypeResolver);
    }

    /**
     * Will check that the function `addError()` works correctly.
     *
     * @test
     */
    public function addErrorAddsAnError()
    {
        $mixedTypeResolver = new MixedTypesResolver();

        $this->assertFalse($mixedTypeResolver->getResult()->hasErrors());

        $errorName = 'hello world!';
        $error = new Error($errorName, 1337);
        $mixedTypeResolver->addError($error);

        $this->assertTrue($mixedTypeResolver->getResult()->hasErrors());
        $this->assertEquals(
            $errorName,
            $mixedTypeResolver->getResult()->getFirstError()->getMessage()
        );

        unset($mixedTypeResolver);
    }

    /**
     * @test
     */
    public function setDataSetsData()
    {
        $mixedTypeResolver = new MixedTypesResolver();
        $data = ['foo' => 'bar'];

        $mixedTypeResolver->setData($data);
        $this->assertEquals($data, $mixedTypeResolver->getData());

        unset($mixedTypeResolver);
    }

    /**
     * @test
     */
    public function setObjectTypeSetsObjectType()
    {
        $mixedTypeResolver = new MixedTypesResolver();
        $objectType = \stdClass::class;

        $mixedTypeResolver->setObjectType($objectType);
        $this->assertEquals($objectType, $mixedTypeResolver->getObjectType());

        unset($mixedTypeResolver);
    }
}
