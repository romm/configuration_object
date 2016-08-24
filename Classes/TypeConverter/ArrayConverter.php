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

use Romm\ConfigurationObject\Traits\ConfigurationObject\StoreArrayIndexTrait;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface;
use TYPO3\CMS\Extbase\Property\TypeConverter\AbstractTypeConverter;
use TYPO3\CMS\Extbase\Utility\TypeHandlingUtility;

/**
 * An internal array converter, used to keep the keys when the conversion is
 * done (the one from Extbase forgets them).
 *
 * @package Romm\ConfigurationObject
 */
class ArrayConverter extends AbstractTypeConverter
{

    /**
     * @var    string[]
     */
    protected $sourceTypes = ['array'];

    /**
     * @var    string
     */
    protected $targetType = '\\ArrayObject';

    /**
     * @var    integer
     */
    protected $priority = 0;

    /**
     * Converts into an array, leaving child properties types.
     *
     * @inheritdoc
     */
    public function convertFrom($source, $targetType, array $convertedChildProperties = [], PropertyMappingConfigurationInterface $configuration = null)
    {
        $result = [];
        $array = ('array' === $targetType)
            ? $source
            : $convertedChildProperties;

        foreach ($array as $name => $subProperty) {
            $result[$name] = $subProperty;

            if (is_object($subProperty)
                && in_array(StoreArrayIndexTrait::class, class_uses($subProperty))
            ) {
                /** @var StoreArrayIndexTrait $subProperty */
                $subProperty->setArrayIndex($name);
            }
        }

        return $result;
    }

    /**
     * @see \TYPO3\CMS\Extbase\Property\TypeConverter\AbstractTypeConverter::getTypeOfChildProperty()
     * @inheritdoc
     */
    public function getTypeOfChildProperty($targetType, $propertyName, PropertyMappingConfigurationInterface $configuration)
    {
        $parsedTargetType = TypeHandlingUtility::parseType($targetType);

        return $parsedTargetType['elementType'];
    }
}
