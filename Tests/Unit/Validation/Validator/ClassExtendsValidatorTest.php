<?php
namespace Romm\ConfigurationObject\Tests\Unit\Validation\Validator;

use Romm\ConfigurationObject\Validation\Validator\ClassExtendsValidator;

class ClassExtendsValidatorTest extends AbstractValidatorTest
{

    /**
     * Will test if the validator works correctly.
     *
     * @test
     */
    public function validatorWorks()
    {
        $testClassProphecy = $this->prophesize();
        $testClassProphecy->willExtend(\stdClass::class);

        /** @var ClassExtendsValidator $mockedClassExtendsValidator */
        $mockedClassExtendsValidator = $this->getMockedValidatorInstance(ClassExtendsValidator::class, ['class' => \stdClass::class]);

        $test = $mockedClassExtendsValidator->validate('WrongClassName1337');
        $this->assertTrue($test->hasErrors());
        $this->assertEquals(ClassExtendsValidator::ERROR_CODE_CLASS_NOT_FOUND, $test->getFirstError()->getCode());

        $test = $mockedClassExtendsValidator->validate(\stdClass::class);
        $this->assertTrue($test->hasErrors());
        $this->assertEquals(ClassExtendsValidator::ERROR_CODE_CLASS_NOT_VALID, $test->getFirstError()->getCode());

        $test = $mockedClassExtendsValidator->validate(get_class($testClassProphecy->reveal()));
        $this->assertFalse($test->hasErrors());
    }
}
