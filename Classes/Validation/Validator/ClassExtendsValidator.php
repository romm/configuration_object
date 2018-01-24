<?php
/*
 * 2018 Romain CANON <romain.hydrocanon@gmail.com>
 *
 * This file is part of the TYPO3 Configuration Object project.
 * It is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License, either
 * version 3 of the License, or any later version.
 *
 * For the full copyright and license information, see:
 * http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Romm\ConfigurationObject\Validation\Validator;

use Romm\ConfigurationObject\Core\Core;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

class ClassExtendsValidator extends AbstractValidator
{
    const ERROR_CODE_CLASS_NOT_FOUND = 1487711134;
    const ERROR_CODE_CLASS_NOT_VALID = 1487711239;

    /**
     * @var array
     */
    protected $supportedOptions = [
        'class' => [
            '',
            'Name of the class that the original class should extend.',
            'string',
            true
        ]
    ];

    /**
     * Checks if the value is an existing class.
     *
     * @param mixed $value The value that should be validated.
     */
    public function isValid($value)
    {
        if (false === Core::get()->classExists($value)) {
            $errorMessage = $this->translateErrorMessage(
                'validator.class_exists.not_valid',
                'configuration_object',
                [$value]
            );

            $this->addError($errorMessage, self::ERROR_CODE_CLASS_NOT_FOUND);
        } elseif (false === in_array($this->options['class'], class_parents($value))) {
            $errorMessage = $this->translateErrorMessage(
                'validator.class_extends_class.not_valid',
                'configuration_object',
                [$value, $this->options['class']]
            );

            $this->addError($errorMessage, self::ERROR_CODE_CLASS_NOT_VALID);
        }
    }
}
