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

namespace Romm\ConfigurationObject\Validation\Validator\Internal;

use Romm\ConfigurationObject\Core\Core;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Extbase\Validation\Validator\GenericObjectValidator;

/**
 * @internal
 */
class ConfigurationObjectValidator extends GenericObjectValidator
{
    /**
     * @inheritdoc
     */
    protected function getPropertyValue($object, $propertyName)
    {
        if (ObjectAccess::isPropertyGettable($object, $propertyName)) {
            return Core::get()->getObjectService()->getObjectProperty($object, $propertyName);
        } else {
            return ObjectAccess::getProperty($object, $propertyName, true);
        }
    }
}
