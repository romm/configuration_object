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

namespace Romm\ConfigurationObject\Core;

use Romm\ConfigurationObject\Exceptions\MethodNotFoundException;
use Romm\ConfigurationObject\Service\Items\Parents\ParentsUtility;
use Romm\ConfigurationObject\Validation\ValidatorResolver;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Extbase\Reflection\ReflectionService;

/**
 * General functions.
 */
class Core implements SingletonInterface
{

    /**
     * @var ObjectManager
     */
    protected static $objectManager;

    /**
     * @var ReflectionService
     */
    protected static $reflectionService;

    /**
     * @var ValidatorResolver
     */
    protected static $validatorResolver;

    /**
     * @var ParentsUtility
     */
    protected static $parentsUtility;

    /**
     * @var array
     */
    protected static $existingClassList = [];

    /**
     * @var array[]
     */
    protected static $gettablePropertiesOfObjects = [];

    /**
     * @return ObjectManager
     */
    public static function getObjectManager()
    {
        if (null === self::$objectManager) {
            self::$objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        }

        return self::$objectManager;
    }

    /**
     * @return ReflectionService
     */
    public static function getReflectionService()
    {
        if (null === self::$reflectionService) {
            self::$reflectionService = GeneralUtility::makeInstance(ReflectionService::class);
        }

        return self::$reflectionService;
    }

    /**
     * @return ValidatorResolver
     */
    public static function getValidatorResolver()
    {
        if (null === self::$validatorResolver) {
            self::$validatorResolver = self::getObjectManager()->get(ValidatorResolver::class);
        }

        return self::$validatorResolver;
    }

    /**
     * @return ParentsUtility
     */
    public static function getParentsUtility()
    {
        if (null === self::$parentsUtility) {
            self::$parentsUtility = GeneralUtility::makeInstance(ParentsUtility::class);
        }

        return self::$parentsUtility;
    }

    /**
     * Internal function which will check if the given class exists. This is
     * useful because of the calls to undefined class, which can lead to a lack
     * of performance due to the auto-loader called if the name of the class
     * is not registered yet.
     *
     * This function will store the already checked class name in local cache.
     *
     * @param string $className
     * @return bool
     */
    public static function classExists($className)
    {
        if (false === isset(self::$existingClassList[$className])) {
            self::$existingClassList[$className] = class_exists($className);
        }

        return self::$existingClassList[$className];
    }

    /**
     * Returns the list of properties which are accessible for this given
     * object.
     *
     * Properties are stored in local cache to improve performance.
     *
     * @param object $object
     * @return array
     */
    public static function getGettablePropertiesOfObject($object)
    {
        $className = get_class($object);

        if (false === isset(self::$gettablePropertiesOfObjects[$className])) {
            self::$gettablePropertiesOfObjects[$className] = [];
            $properties = self::getReflectionService()->getClassPropertyNames($className);

            foreach ($properties as $propertyName) {
                if (true === self::isPropertyGettable($object, $propertyName)) {
                    self::$gettablePropertiesOfObjects[$className][] = $propertyName;
                }
            }
        }

        return self::$gettablePropertiesOfObjects[$className];
    }

    /**
     * Will check if the property of the given object is gettable. Meaning it
     * can be accessed either:
     *
     * - By the true getter if it does exist;
     * - Or by a magic method.
     *
     * @param object $object
     * @param string $propertyName
     * @return bool
     */
    protected static function isPropertyGettable($object, $propertyName)
    {
        $flag = false;

        if (ObjectAccess::isPropertyGettable($object, $propertyName)) {
            $flag = true;
            $getterMethodName = 'get' . ucfirst($propertyName);

            if (false === method_exists($object, $getterMethodName)
                && is_callable([$object, $getterMethodName])
            ) {
                try {
                    $object->$getterMethodName();
                } catch (MethodNotFoundException $e) {
                    $flag = false;
                }
            }
        }

        return $flag;
    }
}
