<?php
namespace Romm\ConfigurationObject\Tests\Unit\Reflection;

use Romm\ConfigurationObject\Reflection\ReflectionService;
use Romm\ConfigurationObject\Tests\Unit\AbstractUnitTest;
use Romm\ConfigurationObject\Tests\Unit\Reflection\Fixture\ReflectionClass;

class ReflectionServiceTest extends AbstractUnitTest
{
    /**
     * Checks that the notation `MyClass[]` is transformed to
     * `\ArrayObject<MyClass>` which is actually understood by TYPO3 core.
     *
     * @test
     */
    public function propertyVarTagArrayNotationIsTransformed()
    {
        $reflectionService = new ReflectionService;

        $this->assertEquals(
            ['var' => ['\\ArrayObject<\\stdClass>']],
            $reflectionService->getPropertyTagsValues(ReflectionClass::class, 'a')
        );

        $this->assertEquals(
            ['var' => ['\\ArrayObject<\\stdClass>']],
            $reflectionService->getPropertyTagsValues(ReflectionClass::class, 'b')
        );

        $this->assertEquals(
            ['var' => ['\\stdClass']],
            $reflectionService->getPropertyTagsValues(ReflectionClass::class, 'c')
        );
    }

    /**
     * Same as @see propertyVarTagArrayNotationIsTransformed but with the `var`
     * tag.
     *
     * @test
     */
    public function propertyVarTagArrayNotationIsTransformedBis()
    {
        $reflectionService = new ReflectionService;

        $this->assertEquals(
            ['\\ArrayObject<\\stdClass>'],
            $reflectionService->getPropertyTagValues(ReflectionClass::class, 'a', 'var')
        );

        $this->assertEquals(
            ['\\ArrayObject<\\stdClass>'],
            $reflectionService->getPropertyTagValues(ReflectionClass::class, 'b', 'var')
        );

        $this->assertEquals(
            ['\\stdClass'],
            $reflectionService->getPropertyTagValues(ReflectionClass::class, 'c', 'var')
        );
    }
}
