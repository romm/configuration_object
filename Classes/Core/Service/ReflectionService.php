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

namespace Romm\ConfigurationObject\Core\Service;

use Romm\ConfigurationObject\Exceptions\PropertyNotAccessibleException;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Reflection\ClassReflection;
use TYPO3\CMS\Extbase\Reflection\PropertyReflection;

/**
 * An abstraction for class reflection, which is used a lot by this API, to
 * reduce performance impact.
 */
class ReflectionService implements SingletonInterface
{
    /**
     * @var ReflectionService
     */
    private static $instance;

    /**
     * @var ClassReflection[]
     */
    protected $classReflection = [];

    /**
     * @var PropertyReflection[]
     */
    protected $classAccessibleProperties = [];

    /**
     * @return ReflectionService
     */
    public static function get()
    {
        if (null === self::$instance) {
            self::$instance = GeneralUtility::makeInstance(self::class);
        }

        return self::$instance;
    }

    /**
     * @param string $className
     * @return ClassReflection
     */
    public function getClassReflection($className)
    {
        if (false === isset($this->classReflection[$className])) {
            $this->classReflection[$className] = GeneralUtility::makeInstance(ClassReflection::class, $className);
        }

        return $this->classReflection[$className];
    }

    /**
     * @param string $className
     * @return PropertyReflection
     */
    public function getAccessibleProperties($className)
    {
        if (false === isset($this->classAccessibleProperties[$className])) {
            $this->classAccessibleProperties[$className] = [];

            $classReflection = $this->getClassReflection($className);
            $properties = $classReflection->getProperties(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED);

            foreach ($properties as $property) {
                $this->classAccessibleProperties[$className][$property->getName()] = $property;
            }
        }

        return $this->classAccessibleProperties[$className];
    }

    /**
     * @param string $className
     * @param string $propertyName
     * @return bool
     */
    public function isClassPropertyAccessible($className, $propertyName)
    {
        $accessibleProperties = $this->getAccessibleProperties($className);

        return isset($accessibleProperties[$propertyName]);
    }

    /**
     * @param string $className
     * @param string $propertyName
     * @return PropertyReflection
     * @throws PropertyNotAccessibleException
     */
    public function getClassAccessibleProperty($className, $propertyName)
    {
        if (false === $this->isClassPropertyAccessible($className, $propertyName)) {
            throw new PropertyNotAccessibleException(
                "Property $className::$propertyName is not accessible!",
                1489149795
            );
        }

        $accessibleProperties = $this->getAccessibleProperties($className);

        return $accessibleProperties[$propertyName];
    }
}
