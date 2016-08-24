<?php
namespace Romm\ConfigurationObject\Tests\Unit;

use Romm\ConfigurationObject\Core\Core;
use Romm\ConfigurationObject\Validation\ValidatorResolver;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

trait UnitTestUtility
{

    /**
     * Will force the Extbase `ObjectManager` getter of the core to return a
     * mocked instance of the class.
     */
    public function injectMockedObjectManagerInCore()
    {
        $reflectedCore = new \ReflectionClass(Core::class);
        $objectManagerProperty = $reflectedCore->getProperty('objectManager');
        $objectManagerProperty->setAccessible(true);
        $objectManagerProperty->setValue($this->getObjectManagerMock());
    }

    /**
     * Will force the Extbase `ValidatorResolver` getter of the core to return a
     * mocked instance of the class.
     */
    public function injectMockedValidatorResolverInCore()
    {
        $validatorResolver = $this->getObjectManagerMock()->get(ValidatorResolver::class);

        $validatorResolver->injectObjectManager(Core::getObjectManager());
        $validatorResolver->injectReflectionService(Core::getReflectionService());

        $reflectedCore = new \ReflectionClass(Core::class);
        $objectManagerProperty = $reflectedCore->getProperty('validatorResolver');
        $objectManagerProperty->setAccessible(true);
        $objectManagerProperty->setValue($validatorResolver);
    }

    /**
     * Returns a mocked instance of the Extbase `ObjectManager`. Will allow the
     * main function `get()` to work properly during the tests.
     *
     * @return ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getObjectManagerMock()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject $mockObjectManager */
        $mockObjectManager = $this->getMock(ObjectManagerInterface::class);
        $mockObjectManager->expects($this->any())
            ->method('get')
            ->will(
                $this->returnCallback(
                    function () {
                        $args = func_get_args();
                        $reflectionClass = new \ReflectionClass(array_shift($args));
                        if (empty($args)) {
                            $instance = $reflectionClass->newInstance();
                        } else {
                            $instance = $reflectionClass->newInstanceArgs($args);
                        }

                        return $instance;
                    }
                )
            );

        return $mockObjectManager;
    }
}
