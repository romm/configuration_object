<?php
namespace Romm\ConfigurationObject\Tests\Unit\Validation\Validator;

use PHPUnit\Framework\MockObject\MockObject;
use Romm\ConfigurationObject\Tests\Unit\AbstractUnitTest;
use TYPO3\CMS\Extbase\Validation\Validator\ValidatorInterface;

abstract class AbstractValidatorTest extends AbstractUnitTest
{

    /**
     * Returns a mocked instance of the given validator class name. It will
     * prevent the function `translateErrorMessage()` from trying to fetch
     * localized messages.
     *
     * @param string $className
     * @param array $arguments
     * @param array $methods
     * @return MockObject|ValidatorInterface
     */
    protected function getMockedValidatorInstance($className, array $arguments = [], array $methods = ['translateErrorMessage'])
    {
        /** @var ValidatorInterface|MockObject $mockedClassExistsValidator */
        $mockedClassExistsValidator = $this->getMockBuilder($className)
            ->setMethods($methods)
            ->setConstructorArgs([$arguments])
            ->getMock();

        $mockedClassExistsValidator->expects($this->any())
            ->method('translateErrorMessage')
            ->will($this->returnValue('foo'));

        return $mockedClassExistsValidator;
    }
}
