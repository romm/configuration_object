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

use Romm\ConfigurationObject\Exceptions\UnsupportedVersionException;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

class IconExistsValidator extends AbstractValidator
{
    /**
     * Checks that the given icon identifier exists in the TYPO3 icon registry.
     *
     * @param mixed $value
     * @throws UnsupportedVersionException
     */
    public function isValid($value)
    {
        if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '7.6.0', '<')) {
            throw new UnsupportedVersionException(
                'The validator "' . self::class . '" cannot be used in TYPO3 versions anterior to `7.6.0`.',
                1506281412
            );
        }

        $value = (string)$value;

        if (false === $this->getIconRegistry()->isRegistered($value)) {
            $errorMessage = $this->translateErrorMessage('validator.icon_exists.not_valid', 'configuration_object', [$value]);
            $this->addError($errorMessage, 1506272737);
        }
    }

    /**
     * @return IconRegistry
     */
    protected function getIconRegistry()
    {
        /** @var IconRegistry $iconRegistry */
        $iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);

        return $iconRegistry;
    }
}
