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

namespace Romm\ConfigurationObject\TypeConverter;

use Romm\ConfigurationObject\Core\Core;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Property\Exception\InvalidTargetException;
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
    public function getTypeOfChildProperty(string $targetType, string $propertyName, PropertyMappingConfigurationInterface $configuration): string
    {
        $specificTargetType = $this->objectContainer->getImplementationClassName($targetType);

        if (Core::get()->classExists($specificTargetType)) {
            $property = $this->reflectionService->getClassSchema($specificTargetType)->getProperty($propertyName);

            $elementType = $property->getElementType();
            $type = $property->getType();

            if ($type === 'array' && $elementType !== null) {
                return $elementType . '[]';
            }
            if ($elementType !== null) {
                return $elementType;
            }
            if ($type !== null) {
                return $type;
            }
        }

        return parent::getTypeOfChildProperty($targetType, $propertyName, $configuration);
    }

    /**
     * @inheritdoc
     */
    public function convertFrom($source, string $targetType, array $convertedChildProperties = [], PropertyMappingConfigurationInterface $configuration = null): ?object
    {
        try {
            return parent::convertFrom($source, $targetType, $convertedChildProperties, $configuration);
        } catch (InvalidTargetException $exception) {
            return new Error('Error during conversion: ' . $exception->getMessage(), $exception->getCode());
        }
    }
}
