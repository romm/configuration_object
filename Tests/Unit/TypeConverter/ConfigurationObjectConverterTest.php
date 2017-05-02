<?php
namespace Romm\ConfigurationObject\Tests\Unit\TypeConverter;

use Romm\ConfigurationObject\Tests\Fixture\Model\DummyConfigurationObjectWithConstructorArguments;
use Romm\ConfigurationObject\Tests\Unit\AbstractUnitTest;
use Romm\ConfigurationObject\TypeConverter\ConfigurationObjectConverter;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Object\Container\Container;
use TYPO3\CMS\Extbase\Reflection\ReflectionService;

class ConfigurationObjectConverterTest extends AbstractUnitTest
{
    /**
     * When a required argument for the constructor is not filled, an error must
     * be returned (which will be added to the mapper result);
     *
     * @test
     */
    public function missingConstructorRequiredArgumentReturnsError()
    {
        $configurationObjectConverter = new ConfigurationObjectConverter;
        $this->inject($configurationObjectConverter, 'objectContainer', new Container);
        $this->inject($configurationObjectConverter, 'reflectionService', new ReflectionService);

        $result = $configurationObjectConverter->convertFrom(
            null,
            DummyConfigurationObjectWithConstructorArguments::class,
            ['foo' => 'foo']
        );

        $this->assertInstanceOf(Error::class, $result);
    }
}
