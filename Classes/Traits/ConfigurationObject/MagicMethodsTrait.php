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

use Romm\ConfigurationObject\Core\Service\ReflectionService;
use Romm\ConfigurationObject\Exceptions\MethodNotFoundException;
use Romm\ConfigurationObject\Exceptions\PropertyNotAccessibleException;

/**
 * This trait will implement magic setters and getters for accessible properties
 * of the class.
 *
 * /!\ This class uses the method `__call()`, if you need to override this
 * function in your own class, you must call the function `handleMagicMethods()`
 * and handle the result correctly. This is the only way to keep this trait
 * features running correctly.
 */
trait MagicMethodsTrait
{

    /**
     * See class description.
     *
     * @param string $name      Name of the called function.
     * @param array  $arguments Arguments passed to the function
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return $this->handleMagicMethods($name, $arguments);
    }

    /**
     * See class description.
     *
     * @param string $name      Name of the called function.
     * @param array  $arguments Arguments passed to the function.
     * @return mixed
     * @throws MethodNotFoundException
     */
    public function handleMagicMethods($name, array $arguments)
    {
        $flag = false;
        $result = null;

        if (in_array($type = substr($name, 0, 3), ['set', 'get'])) {
            /*
             * We will first try to access the property written with
             * lowerCamelCase, which is the convention for many people. If this
             * property is not found, we try the real name of the property given
             * in the magic method.
             */
            $propertyLowerCase = lcfirst(substr($name, 3));
            $property = substr($name, 3);

            foreach ([$propertyLowerCase, $property] as $prop) {
                try {
                    $result = $this->handlePropertyMagicMethod($prop, $type, $arguments);
                    $flag = true;
                    break;
                } catch (PropertyNotAccessibleException $e) {
                    continue;
                }
            }
        }

        if (false === $flag) {
            throw new MethodNotFoundException(
                'The method "' . $name . '" does not exist in the class "' . get_class($this) . '".',
                1471043854
            );
        }

        return $result;
    }

    /**
     * Will try to set/get the wanted property.
     *
     * @param string $property Name of the property to be set/get.
     * @param string $type     Must be `set` or `get`.
     * @param array  $arguments
     * @return mixed
     * @throws PropertyNotAccessibleException
     */
    protected function handlePropertyMagicMethod($property, $type, array $arguments)
    {
        if ($this->isPropertyAccessible($property)) {
            switch ($type) {
                case 'set':
                    $this->{$property} = current($arguments);
                    return null;
                    break;
                case 'get':
                    return $this->{$property};
                    break;
            }
        }

        throw new PropertyNotAccessibleException(
            'The property "' . $property . '" is not accessible in the class "' . get_class($this) . '".',
            1473260999
        );
    }

    /**
     * Returns true if the given property name is accessible for the current
     * instance of this class.
     *
     * Note that the list of accessible properties for this class is stored in
     * cache to improve performances.
     *
     * @param string $propertyName
     * @return bool
     */
    private function isPropertyAccessible($propertyName)
    {
        $result = false;
        $className = get_class($this);
        $reflectionService = ReflectionService::get();

        if ($reflectionService->isClassPropertyAccessible($className, $propertyName)) {
            $property = $reflectionService->getClassAccessibleProperty($className, $propertyName);
            $result = false === $property->isTaggedWith('disableMagicMethods');
        }

        return $result;
    }
}
