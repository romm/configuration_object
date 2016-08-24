<?php
namespace Romm\ConfigurationObject\Tests\Fixture\Validator;

use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

class WrongValueValidator extends AbstractValidator
{

    const WRONG_VALUE = 'wrong value';
    const ERROR_MESSAGE = 'Wrong value!';

    /**
     * @inheritdoc
     */
    protected $acceptsEmptyValues = false;

    /**
     * @param mixed $value
     */
    protected function isValid($value)
    {
        if (self::WRONG_VALUE === $value) {
            $this->addError(self::ERROR_MESSAGE, 1337);
        }
    }
}