<?php
namespace Romm\ConfigurationObject\Tests\Unit\Validation\Validator;

use Romm\ConfigurationObject\Validation\Validator\ClassExistsValidator;

class ClassExistsValidatorTest extends AbstractValidatorTest
{

    /**
     * Will test if the validator works correctly.
     *
     * @test
     */
    public function validatorWorks()
    {
        /** @var ClassExistsValidator $mockedClassExistsValidator */
        $mockedClassExistsValidator = $this->getMockedValidatorInstance(ClassExistsValidator::class);

        $test = $mockedClassExistsValidator->validate(\stdClass::class);
        $this->assertFalse($test->hasErrors());

        $test = $mockedClassExistsValidator->validate(self::class);
        $this->assertFalse($test->hasErrors());

        $test = $mockedClassExistsValidator->validate('WrongClassName1337');
        $this->assertTrue($test->hasErrors());
    }
}
