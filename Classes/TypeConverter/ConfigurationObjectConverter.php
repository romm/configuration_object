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
    public function getTypeOfChildProperty($targetType, $propertyName, PropertyMappingConfigurationInterface $configuration)
    {
        $specificTargetType = $this->objectContainer->getImplementationClassName($targetType);

        if (Core::get()->classExists($specificTargetType)) {
            $propertyTags = $this->reflectionService->getPropertyTagValues($specificTargetType, $propertyName, 'var');

            if (!empty($propertyTags)) {
                return current($propertyTags);
            }
        }

        return parent::getTypeOfChildProperty($targetType, $propertyName, $configuration);
    }

    /**
     * @inheritdoc
     */
    public function convertFrom($source, $targetType, array $convertedChildProperties = [], PropertyMappingConfigurationInterface $configuration = null)
    {
        try {
            return parent::convertFrom($source, $targetType, $convertedChildProperties, $configuration);
        } catch (InvalidTargetException $exception) {
            return new Error('The following properties must be filled: "' . implode('", "', $this->getRequiredConstructorArguments($targetType)) . '".', $exception->getCode());
        }
    }

    /**
     * @param string $type
     * @return array
     */
    protected function getRequiredConstructorArguments($type)
    {
        $requiredArguments = [];
        $type = $this->objectContainer->getImplementationClassName($type);
        $arguments = $this->reflectionService->getMethodParameters($type, '__construct');

        foreach ($arguments as $argumentName => $argumentInformation) {
            if ($argumentInformation['optional'] === false) {
                $requiredArguments[] = $argumentName;
            }
        }

        return $requiredArguments;
    }
}
