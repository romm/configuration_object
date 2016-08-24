<?php
namespace Romm\ConfigurationObject\Tests\Unit\Validation\Validator;

use Romm\ConfigurationObject\Validation\Validator\HasValuesValidator;

class HasValuesValidatorTest extends AbstractValidatorTest
{

    /**
     * Will test if the validator works correctly.
     *
     * @test
     */
    public function validatorWorks()
    {
        /** @var HasValuesValidator $mockedHasValuesValidator */
        $mockedHasValuesValidator = $this->getMockedValidatorInstance(HasValuesValidator::class, ['values' => 'foo|bar']);

        $test = $mockedHasValuesValidator->validate('foo');
        $this->assertFalse($test->hasErrors());

        $test = $mockedHasValuesValidator->validate('bar');
        $this->assertFalse($test->hasErrors());

        $test = $mockedHasValuesValidator->validate('42');
        $this->assertTrue($test->hasErrors());
    }
}
