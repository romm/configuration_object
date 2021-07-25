<?php
namespace Romm\ConfigurationObject\Tests\Unit\Validation\Validator;

use PHPUnit\Framework\MockObject\MockObject;
use Romm\ConfigurationObject\Validation\Validator\IconExistsValidator;
use TYPO3\CMS\Core\Imaging\IconRegistry;

class IconExistsValidatorTest extends AbstractValidatorTest
{

    /**
     * Will test if the validator works correctly.
     *
     * @test
     */
    public function validatorWorks()
    {
        $correctIcon = 'foo';
        $incorrectIcon = 'bar';

        /** @var IconExistsValidator|MockObject $mockedIconExistsValidator */
        $mockedIconExistsValidator = $this->getMockedValidatorInstance(IconExistsValidator::class, [], ['translateErrorMessage', 'getIconRegistry']);

        $iconRegistry = $this->prophesize(IconRegistry::class);

        $iconRegistry->isRegistered($correctIcon)
            ->shouldBeCalled()
            ->willReturn(true);

        $iconRegistry->isRegistered($incorrectIcon)
            ->shouldBeCalled()
            ->willReturn(false);

        $mockedIconExistsValidator->method('getIconRegistry')
            ->willReturn($iconRegistry->reveal());

        $test = $mockedIconExistsValidator->validate($correctIcon);
        $this->assertFalse($test->hasErrors());

        $test = $mockedIconExistsValidator->validate($incorrectIcon);
        $this->assertTrue($test->hasErrors());
    }
}
