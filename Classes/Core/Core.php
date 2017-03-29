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

namespace Romm\ConfigurationObject\Core;

use Romm\ConfigurationObject\Core\Service\CacheService;
use Romm\ConfigurationObject\Exceptions\MethodNotFoundException;
use Romm\ConfigurationObject\Service\Items\Parents\ParentsUtility;
use Romm\ConfigurationObject\Service\ServiceFactory;
use Romm\ConfigurationObject\Validation\ValidatorResolver;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Extbase\Reflection\ReflectionService;

/**
 * General functions.
 *
 * The structure is here to help unit tests to mock correctly what is needed.
 */
class Core implements SingletonInterface
{
    /**
     * @var Core
     */
    protected static $instance;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var ReflectionService
     */
    protected $reflectionService;

    /**
     * @var ValidatorResolver
     */
    protected $validatorResolver;

    /**
     * @var CacheManager
     */
    protected $cacheManager;

    /**
     * @var ParentsUtility
     */
    protected $parentsUtility;

    /**
     * @var CacheService
     */
    protected $cacheService;

    /**
     * @var array
     */
    protected $existingClassList = [];

    /**
     * @var array[]
     */
    protected $gettablePropertiesOfObjects = [];

    /**
     * @return Core
     */
    public static function get()
    {
        if (null === self::$instance) {
            /** @var ObjectManager $objectManager */
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

            self::$instance = $objectManager->get(self::class);
        }

        return self::$instance;
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
    public function classExists($className)
    {
        if (false === isset($this->existingClassList[$className])) {
            $this->existingClassList[$className] = class_exists($className) || interface_exists($className);
        }

        return $this->existingClassList[$className];
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
    public function getGettablePropertiesOfObject($object)
    {
        $className = get_class($object);

        if (false === isset($this->gettablePropertiesOfObjects[$className])) {
            $this->gettablePropertiesOfObjects[$className] = [];
            $properties = $this->getReflectionService()->getClassPropertyNames($className);

            foreach ($properties as $propertyName) {
                if (true === $this->isPropertyGettable($object, $propertyName)) {
                    $this->gettablePropertiesOfObjects[$className][] = $propertyName;
                }
            }
        }

        return $this->gettablePropertiesOfObjects[$className];
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
    protected function isPropertyGettable($object, $propertyName)
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

    /**
     * @return ServiceFactory
     */
    public function getServiceFactoryInstance()
    {
        /** @var ServiceFactory $serviceFactory */
        $serviceFactory = GeneralUtility::makeInstance(ServiceFactory::class);

        return $serviceFactory;
    }

    /**
     * @return ObjectManagerInterface
     */
    public function getObjectManager()
    {
        return $this->objectManager;
    }

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function injectObjectManager(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @return ReflectionService
     */
    public function getReflectionService()
    {
        return $this->reflectionService;
    }

    /**
     * @param ReflectionService $reflectionService
     */
    public function injectReflectionService(ReflectionService $reflectionService)
    {
        $this->reflectionService = $reflectionService;
    }

    /**
     * @return ValidatorResolver
     */
    public function getValidatorResolver()
    {
        return $this->validatorResolver;
    }

    /**
     * @param ValidatorResolver $validatorResolver
     */
    public function injectValidatorResolver(ValidatorResolver $validatorResolver)
    {
        $this->validatorResolver = $validatorResolver;
    }

    /**
     * @return CacheManager
     */
    public function getCacheManager()
    {
        return $this->cacheManager;
    }

    /**
     * @param CacheManager $cacheManager
     */
    public function injectCacheManager(CacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    /**
     * @return ParentsUtility
     */
    public function getParentsUtility()
    {
        return $this->parentsUtility;
    }

    /**
     * @param ParentsUtility $parentsUtility
     */
    public function injectParentsUtility(ParentsUtility $parentsUtility)
    {
        $this->parentsUtility = $parentsUtility;
    }

    /**
     * @return CacheService
     */
    public function getCacheService()
    {
        $this->cacheService->registerInternalCache();

        return $this->cacheService;
    }

    /**
     * @param CacheService $cacheService
     */
    public function injectCacheService(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }
}
