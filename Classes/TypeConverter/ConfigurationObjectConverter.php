<?php
/*
 * 2016 Romain CANON <romain.hydrocanon@gmail.com>
 *
 * This file is part of the TYPO3 Configuration Object project.
 * It is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License, either
 * version 3 of the License, or any later version.
 *
 * For the full copyright and license information, see:
 * http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Romm\ConfigurationObject\TypeConverter;

use Romm\ConfigurationObject\Core\Core;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface;
use TYPO3\CMS\Extbase\Property\TypeConverter\ObjectConverter;

/**
 * Configuration object converter.
 */
class ConfigurationObjectConverter extends ObjectConverter
{

    /**
     * Will check the type of the given class property, if reflection gives no
     * result, the parent function is called.
     *
     * @inheritdoc
     */
    public function getTypeOfChildProperty($targetType, $propertyName, PropertyMappingConfigurationInterface $configuration)
    {
        $specificTargetType = $this->objectContainer->getImplementationClassName($targetType);

        if (Core::classExists($specificTargetType)) {
            $propertyTags = $this->reflectionService->getPropertyTagValues($specificTargetType, $propertyName, 'var');

            if (!empty($propertyTags)) {
                return current($propertyTags);
            }
        }

        return parent::getTypeOfChildProperty($targetType, $propertyName, $configuration);
    }
}
