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

namespace Romm\ConfigurationObject\Traits\ConfigurationObject;

use Romm\ConfigurationObject\Core\Core;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * Use this trait in any configuration object (or one of its sub-objects), to
 * have access to the function `toArray()` which will recursively convert the
 * object to a plain array.
 */
trait ArrayConversionTrait
{

    /**
     * See class description.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->toArrayInternal($this->getObjectPropertiesValues($this));
    }

    /**
     * Internal recursive function used by `toArray()`.
     *
     * Will handle every type of property, and run through them to get a full
     * array containing all values and sub-values.
     *
     * @param  array $properties
     * @return array
     */
    private function toArrayInternal(array $properties)
    {
        $finalProperties = [];

        foreach ($properties as $name => $entity) {
            $cleanValue = $entity;

            if (is_object($entity)) {
                if (in_array(self::class, class_uses($entity))) {
                    $cleanValue = $entity->toArray();
                } elseif ($entity instanceof \Traversable) {
                    $cleanValue = iterator_to_array($entity, false);
                } else {
                    $cleanValue = $this->getObjectPropertiesValues($entity);
                }
            }

            $finalProperties[$name] = (is_array($cleanValue))
                ? $this->toArrayInternal($cleanValue)
                : $cleanValue;
        }

        return $finalProperties;
    }

    /**
     * Will return all the accessible properties of the given object instance.
     *
     * @param object $object
     * @return array
     */
    private function getObjectPropertiesValues($object)
    {
        $properties = Core::get()->getGettablePropertiesOfObject($object);
        $finalProperties = [];

        foreach ($properties as $property) {
            $finalProperties[$property] = ObjectAccess::getProperty($object, $property);
        }

        return $finalProperties;
    }
}
