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

use Romm\ConfigurationObject\Exceptions\SilentExceptionInterface;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

class ObjectService implements SingletonInterface
{
    /**
     * This function will try to get a given property for a given object.
     *
     * Its particularity is that if the getter method for this property is used,
     * the method may throw an exception that implements the interface
     * `SilentExceptionInterface`. In that case, the exception is catch and
     * `null` is returned.
     *
     * This allows more flexibility for the developer, who may still throw
     * exceptions in getter methods for implementation concerns, but these
     * exceptions wont block Configuration Object API processing.
     *
     * @see \Romm\ConfigurationObject\Exceptions\SilentExceptionInterface
     * @see \TYPO3\CMS\Extbase\Reflection\ObjectAccess::getProperty()
     *
     * @param object $object
     * @param string $property
     * @return mixed
     * @throws \Exception
     */
    public function getObjectProperty($object, $property)
    {
        $result = null;

        try {
            $result = ObjectAccess::getProperty($object, $property);
        } catch (\Exception $exception) {
            if (false === $exception instanceof SilentExceptionInterface) {
                throw $exception;
            }
        }

        return $result;
    }
}
