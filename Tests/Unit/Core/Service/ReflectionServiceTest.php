<?php

namespace Romm\ConfigurationObject\Tests\Unit\Core\Service\Cache;

use Romm\ConfigurationObject\Core\Service\ReflectionService;
use Romm\ConfigurationObject\Exceptions\PropertyNotAccessibleException;
use Romm\ConfigurationObject\Legacy\Reflection\ClassReflection;
use Romm\ConfigurationObject\Tests\Fixture\Reflection\ExampleReflection;
use Romm\ConfigurationObject\Tests\Unit\AbstractUnitTest;

class ReflectionServiceTest extends AbstractUnitTest
{
    /**
     * @test
     */
    public function classReflectionIsInstantiatedOnlyOnce()
    {
        $service = new ReflectionService;
        $classReflection = $service->getClassReflection(\stdClass::class);
        $classReflectionBis = $service->getClassReflection(\stdClass::class);
        $classReflectionTer = $service->getClassReflection(self::class);
        $this->assertSame($classReflection, $classReflectionBis);
        $this->assertNotSame($classReflection, $classReflectionTer);
    }

    /**
     * @test
     */
    public function accessiblePropertiesAreAccessed()
    {
        $service = new ReflectionService;
        $accessibleProperties = $service->getAccessibleProperties(ExampleReflection::class);
        $this->assertEquals(
            ['foo', 'bar'],
            array_keys($accessibleProperties)
        );
    }

    /**
     * @test
     */
    public function accessiblePropertiesAreCalculatedOnce()
    {
        /** @var ReflectionService|\PHPUnit_Framework_MockObject_MockObject $service */
        $service = $this->getMockBuilder(ReflectionService::class)
            ->setMethods(['getClassReflection'])
            ->getMock();

        $classReflection = new ClassReflection(ExampleReflection::class);

        $service->expects($this->once())
            ->method('getClassReflection')
            ->willReturn($classReflection);

        $service->getAccessibleProperties(ExampleReflection::class);
        $service->getAccessibleProperties(ExampleReflection::class);
        $service->getAccessibleProperties(ExampleReflection::class);
    }

    /**
     * @test
     */
    public function accessibleProperty()
    {
        $service = new ReflectionService;

        $this->assertTrue($service->isClassPropertyAccessible(ExampleReflection::class, 'foo'));
        $this->assertTrue($service->isClassPropertyAccessible(ExampleReflection::class, 'bar'));
        $this->assertFalse($service->isClassPropertyAccessible(ExampleReflection::class, 'baz'));
    }

    /**
     * @test
     */
    public function singlePropertyReflectionAreReturned()
    {
        $service = new ReflectionService;

        $fooReflection = $service->getClassAccessibleProperty(ExampleReflection::class, 'foo');
        $this->assertEquals('foo', $fooReflection->getName());

        $barReflection = $service->getClassAccessibleProperty(ExampleReflection::class, 'bar');
        $this->assertEquals('bar', $barReflection->getName());
    }

    /**
     * @test
     */
    public function notAccessiblePropertyThrowsException()
    {
        $this->expectException(PropertyNotAccessibleException::class);

        $service = new ReflectionService;

        $service->getClassAccessibleProperty(ExampleReflection::class, 'baz');
    }
}
