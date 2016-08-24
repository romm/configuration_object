<?php
namespace Romm\ConfigurationObject\Tests\Unit\TypeConverter;

use Romm\ConfigurationObject\Tests\Fixture\Model\DummyConfigurationObjectWithStoreArrayIndexTrait;
use Romm\ConfigurationObject\TypeConverter\ArrayConverter;
use TYPO3\CMS\Core\Tests\UnitTestCase;

class ArrayConverterTest extends UnitTestCase
{

    /**
     * Will test if a basic array is converted correctly (the result of the
     * conversion should be the same array).
     *
     * @test
     */
    public function canConvertArray()
    {
        $source = [
            'foo' => ['foo1', 'foo2'],
            'bar' => ['bar1', 'bar2']
        ];

        $arrayConverter = new ArrayConverter();
        $result = $arrayConverter->convertFrom($source, 'array');

        $this->assertEquals($source, $result);
    }

    /**
     * Will test if the array index is stored for objects which use the trait
     * `StoreArrayIndexTrait`.
     *
     * @test
     */
    public function storesArrayIndex()
    {
        $dummyConfigurationObject = new DummyConfigurationObjectWithStoreArrayIndexTrait();
        $source = [
            'foo' => $dummyConfigurationObject
        ];

        $arrayConverter = new ArrayConverter();
        $arrayConverter->convertFrom($source, 'array');

        $index = $dummyConfigurationObject->getArrayIndex();

        $this->assertEquals('foo', $index);
    }
}
