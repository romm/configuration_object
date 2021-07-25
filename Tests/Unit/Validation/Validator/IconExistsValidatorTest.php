<?php
namespace Romm\ConfigurationObject\Tests\Unit\Validation\Validator;

use Romm\ConfigurationObject\Exceptions\UnsupportedVersionException;
use Romm\ConfigurationObject\Validation\Validator\IconExistsValidator;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

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

        /** @var IconExistsValidator|\PHPUnit_Framework_MockObject_MockObject $mockedIconExistsValidator */
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
