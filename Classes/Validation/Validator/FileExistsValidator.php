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

class FileExistsValidator extends AbstractValidator
{
    /**
     * Checks if the value is a path to an existing file.
     *
     * The following syntax is supported: `EXT:my_extension/Path/To/My/File.txt`
     *
     * @param mixed $value
     */
    public function isValid($value)
    {
        $filePath = GeneralUtility::getFileAbsFileName($value);

        if (false === GeneralUtility::getUrl($filePath)) {
            $errorMessage = $this->translateErrorMessage('validator.file_exists.not_valid', 'configuration_object', [$value]);
            $this->addError($errorMessage, 1510085560);
        }
    }
}
