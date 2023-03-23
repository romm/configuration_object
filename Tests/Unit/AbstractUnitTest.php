<?php
namespace Romm\ConfigurationObject\Tests\Unit;

use Prophecy\PhpUnit\ProphecyTrait;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

abstract class AbstractUnitTest extends UnitTestCase
{
    use ConfigurationObjectUnitTestUtility;
    use ProphecyTrait;

    protected $resetSingletonInstances = true;

    protected function setUp(): void
    {
        $this->setUpConfigurationObjectCore();
    }

    /**
     * Injects $dependency into property $name of $target
     *
     * This is a convenience method for setting a protected or private property in
     * a test subject for the purpose of injecting a dependency.
     *
     * @param object $target The instance which needs the dependency
     * @param string $name Name of the property to be injected
     * @param mixed $dependency The dependency to inject – usually an object but can also be any other type
     * @return void
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    protected function inject($target, $name, $dependency)
    {
        if (! is_object($target)) {
            throw new \InvalidArgumentException('Wrong type for argument $target, must be object.', 1476107338);
        }

        $objectReflection = new \ReflectionObject($target);
        $methodNamePart = strtoupper($name[0]) . substr($name, 1);
        if ($objectReflection->hasMethod('set' . $methodNamePart)) {
            $methodName = 'set' . $methodNamePart;
            $target->$methodName($dependency);
        } elseif ($objectReflection->hasMethod('inject' . $methodNamePart)) {
            $methodName = 'inject' . $methodNamePart;
            $target->$methodName($dependency);
        } elseif ($objectReflection->hasProperty($name)) {
            $property = $objectReflection->getProperty($name);
            $property->setAccessible(true);
            $property->setValue($target, $dependency);
        } else {
            throw new \RuntimeException(
                'Could not inject ' . $name . ' into object of type ' . get_class($target),
                1476107339
            );
        }
    }

    public function get($target, $name)
    {
        $objectReflection = new \ReflectionObject($target);

        $property = $objectReflection->getProperty($name);
        $property->setAccessible(true);

        return $property->getValue();
    }
}
