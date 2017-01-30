<?php
/*
 * 2017 Romain CANON <romain.hydrocanon@gmail.com>
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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

class HasValuesValidator extends AbstractValidator
{

    /**
     * @var array
     */
    protected $supportedOptions = [
        'values' => [[], 'Array of accepted values', 'array']
    ];

    /**
     * Checks if the given values is one of the accepted values.
     *
     * @param mixed $value The value that should be validated.
     */
    public function isValid($value)
    {
        $acceptedValues = GeneralUtility::trimExplode('|', $this->options['values']);

        if (false === in_array($value, $acceptedValues)) {
            $errorMessage = $this->translateErrorMessage('validator.has_values.not_valid', 'configuration_object', [implode(', ', $acceptedValues)]);
            $this->addError($errorMessage, 1456140151);
        }
    }
}
