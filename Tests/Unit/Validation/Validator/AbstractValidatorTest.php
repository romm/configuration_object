<?php
namespace Romm\ConfigurationObject\Tests\Unit\Validation\Validator;

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
     * @param array  $arguments
     * @return \PHPUnit_Framework_MockObject_MockObject|ValidatorInterface
     */
    protected function getMockedValidatorInstance($className, array $arguments = [])
    {
        /** @var ValidatorInterface|\PHPUnit_Framework_MockObject_MockObject $mockedClassExistsValidator */
        $mockedClassExistsValidator = $this->getMockBuilder($className)
            ->setMethods(['translateErrorMessage'])
            ->setConstructorArgs([$arguments])
            ->getMock();

        $mockedClassExistsValidator->expects($this->any())
            ->method('translateErrorMessage')
            ->will($this->returnValue('foo'));

        return $mockedClassExistsValidator;
    }
}
