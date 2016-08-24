<?php
namespace Romm\ConfigurationObject\Tests\Unit\Validation\Validator;

use TYPO3\CMS\Core\Tests\UnitTestCase;
use TYPO3\CMS\Extbase\Validation\Validator\ValidatorInterface;

abstract class AbstractValidatorTest extends UnitTestCase
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
        $mockedClassExistsValidator = $this->getMock($className, ['translateErrorMessage'], [$arguments]);

        $mockedClassExistsValidator->expects($this->any())
            ->method('translateErrorMessage')
            ->will($this->returnValue('foo'));

        return $mockedClassExistsValidator;
    }
}
