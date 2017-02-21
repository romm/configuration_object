<?php
namespace Romm\ConfigurationObject\Tests\Unit\Validation\Validator;

use Romm\ConfigurationObject\Validation\Validator\ClassImplementsValidator;
use TYPO3\CMS\Core\SingletonInterface;

class ClassImplementsValidatorTest extends AbstractValidatorTest
{

    /**
     * Will test if the validator works correctly.
     *
     * @test
     */
    public function validatorWorks()
    {
        $testClassProphecy = $this->prophesize();
        $testClassProphecy->willImplement(SingletonInterface::class);

        /** @var ClassImplementsValidator $mockedClassImplementsValidator */
        $mockedClassImplementsValidator = $this->getMockedValidatorInstance(ClassImplementsValidator::class, ['interface' => SingletonInterface::class]);

        $test = $mockedClassImplementsValidator->validate('WrongClassName1337');
        $this->assertTrue($test->hasErrors());
        $this->assertEquals(ClassImplementsValidator::ERROR_CODE_CLASS_NOT_FOUND, $test->getFirstError()->getCode());

        $test = $mockedClassImplementsValidator->validate(\stdClass::class);
        $this->assertTrue($test->hasErrors());
        $this->assertEquals(ClassImplementsValidator::ERROR_CODE_CLASS_NOT_VALID, $test->getFirstError()->getCode());

        $test = $mockedClassImplementsValidator->validate(get_class($testClassProphecy->reveal()));
        $this->assertFalse($test->hasErrors());
    }
}
