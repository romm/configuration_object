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

namespace Romm\ConfigurationObject\Service\Items\Parents;

use Romm\ConfigurationObject\Core\Core;
use Romm\ConfigurationObject\Service\AbstractService;
use Romm\ConfigurationObject\Service\DataTransferObject\ConfigurationObjectConversionDTO;
use Romm\ConfigurationObject\Service\DataTransferObject\GetConfigurationObjectDTO;
use Romm\ConfigurationObject\Service\Event\ConfigurationObjectAfterServiceEventInterface;
use Romm\ConfigurationObject\Service\Event\ObjectConversionAfterServiceEventInterface;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * This service will take care of saving the parent classes of the configuration
 * objects which use the trait `ParentsTrait`.
 */
class ParentsService extends AbstractService implements ObjectConversionAfterServiceEventInterface, ConfigurationObjectAfterServiceEventInterface, SingletonInterface
{

    /**
     * This event needs to be called before the object is stored in cache, as it
     * will add internal variables to the object which are used further to fill
     * the parents variables.
     */
    const PRIORITY_SAVE_OBJECTS_WITH_PARENTS_PATHS = -500;

    /**
     * This event needs to be called after the object is obtained from cache.
     */
    const PRIORITY_FILL_PARENTS = -10000;

    /**
     * @var array
     */
    protected $objectsWithParentsPaths = [];

    /**
     * If the converted item is an object, its properties will be checked: if
     * they do use the trait `ParentsTrait`, then the full path to this property
     * is saved for further usage.
     *
     * @param ConfigurationObjectConversionDTO $serviceDataTransferObject
     */
    public function objectConversionAfter(ConfigurationObjectConversionDTO $serviceDataTransferObject)
    {
        $result = $serviceDataTransferObject->getResult();

        if (is_object($result)) {
            foreach (Core::get()->getGettablePropertiesOfObject($result) as $propertyName) {
                $property = ObjectAccess::getProperty($result, $propertyName);

                $this->checkProperty($serviceDataTransferObject, $property, $propertyName);
            }
        }
    }

    /**
     * Will check the given property: it will recursively go through arrays
     * which can contains objects using the trait `ParentsTrait`.
     *
     * @param ConfigurationObjectConversionDTO $serviceDataTransferObject
     * @param array|object                     $property
     * @param string                           $propertyPath
     */
    protected function checkProperty(ConfigurationObjectConversionDTO $serviceDataTransferObject, $property, $propertyPath)
    {
        if (is_array($property)) {
            foreach ($property as $key => $value) {
                $this->checkProperty($serviceDataTransferObject, $value, $propertyPath . '.' . $key);
            }
        } else {
            $this->checkObjectProperty($serviceDataTransferObject, $property, $propertyPath);
        }
    }

    /**
     * Will check the given property and see if it is an object which uses the
     * trait `ParentsTrait`: if it does, it will store it in the local array
     * containing all the paths to these objects.
     *
     * @param ConfigurationObjectConversionDTO $serviceDataTransferObject
     * @param object                           $property
     * @param string                           $pathSuffix
     */
    protected function checkObjectProperty(ConfigurationObjectConversionDTO $serviceDataTransferObject, $property, $pathSuffix)
    {
        if (is_object($property)) {
            $path = (false === empty($serviceDataTransferObject->getCurrentPropertyPath()))
                ? implode('.', $serviceDataTransferObject->getCurrentPropertyPath()) . '.'
                : '';
            $path .= $pathSuffix;

            if (true === Core::get()->getParentsUtility()->classUsesParentsTrait($property)) {
                $this->objectsWithParentsPaths[] = $path;
            }
        }
    }

    /**
     * This function will first save the entire storage of properties paths set
     * in the function `objectConversionAfter`.
     *
     * Then, each of the properties above will be processed to fill their parent
     * objects.
     *
     * @param GetConfigurationObjectDTO $serviceDataTransferObject
     */
    public function configurationObjectAfter(GetConfigurationObjectDTO $serviceDataTransferObject)
    {
        // Will save the paths of the properties which need their parent objects.
        $this->delay(
            self::PRIORITY_SAVE_OBJECTS_WITH_PARENTS_PATHS,
            function (GetConfigurationObjectDTO $serviceDataTransferObject) {
                if (false === empty($this->objectsWithParentsPaths)) {
                    $serviceDataTransferObject->getResult()
                        ->setInternalVar('objectsWithParentsPaths', $this->objectsWithParentsPaths);

                    $this->objectsWithParentsPaths = [];
                }
            }
        );

        // Will fill all the parents.
        $this->delay(
            self::PRIORITY_FILL_PARENTS,
            function (GetConfigurationObjectDTO $serviceDataTransferObject) {
                $objectsWithParentsPaths = $serviceDataTransferObject->getResult()->getInternalVar('objectsWithParentsPaths');

                if (false === empty($objectsWithParentsPaths)) {
                    $object = $serviceDataTransferObject->getResult()->getObject(true);

                    foreach ($objectsWithParentsPaths as $path) {
                        $this->insertParents($object, explode('.', $path), [$object]);
                    }
                }
            }
        );
    }

    /**
     * Internal function to fill the parents.
     *
     * @param mixed    $entity
     * @param array    $path
     * @param object[] $parents
     */
    protected function insertParents($entity, array $path, array $parents)
    {
        $propertyName = reset($path);
        $propertyValue = ObjectAccess::getProperty($entity, $propertyName);

        if (1 === count($path)) {
            if (is_object($propertyValue)
                && Core::get()->getParentsUtility()->classUsesParentsTrait($propertyValue)
            ) {
                $parents = $this->filterParents($parents);

                /** @var ParentsTrait $propertyValue */
                $propertyValue->setParents($parents);
            }
        } else {
            if (is_object($propertyValue)) {
                $parents[] = $propertyValue;
            }

            array_shift($path);
            $this->insertParents($propertyValue, $path, $parents);
        }
    }

    /**
     * This function will filter a given array of objects, by removing
     * unnecessary parents that use `ParentsTrait` and are followed by another
     * parent that does the same: there is no need to have the full list of
     * chained parents
     *
     * @param object[] $parents
     * @return object[]
     */
    protected function filterParents(array $parents)
    {
        $filteredParents = [];
        $lastParentWithTrait = null;

        foreach ($parents as $parent) {
            if (Core::get()->getParentsUtility()->classUsesParentsTrait($parent)) {
                $lastParentWithTrait = $parent;
            } else {
                if (null !== $lastParentWithTrait) {
                    $filteredParents[] = $lastParentWithTrait;
                    $lastParentWithTrait = null;
                }

                $filteredParents[] = $parent;
            }
        }

        if (null !== $lastParentWithTrait) {
            $filteredParents[] = $lastParentWithTrait;
        }

        return $filteredParents;
    }
}
