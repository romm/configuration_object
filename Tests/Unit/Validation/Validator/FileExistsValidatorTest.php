<?php
namespace Romm\ConfigurationObject\Tests\Unit\Validation\Validator;

use Romm\ConfigurationObject\Validation\Validator\FileExistsValidator;

class FileExistsValidatorTest extends AbstractValidatorTest
{
    /**
     * Will test if the validator works correctly.
     *
     * @test
     */
    public function validatorWorks()
    {
        /** @var FileExistsValidator $mockedFileExistsValidator */
        $mockedFileExistsValidator = $this->getMockedValidatorInstance(FileExistsValidator::class);

        $test = $mockedFileExistsValidator->validate('not existing file');
        $this->assertTrue($test->hasErrors());

        $test = $mockedFileExistsValidator->validate('EXT:configuration_object/NotExistingFile.png');
        $this->assertTrue($test->hasErrors());

        $test = $mockedFileExistsValidator->validate('EXT:configuration_object/ext_icon.png');
        $this->assertFalse($test->hasErrors());
    }
}
