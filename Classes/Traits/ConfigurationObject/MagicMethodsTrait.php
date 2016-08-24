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

namespace Romm\ConfigurationObject\Traits\ConfigurationObject;

use Romm\ConfigurationObject\Exceptions\MethodNotFoundException;

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
     * Contains the list of the accessible properties for the instances of this
     * class.
     *
     * @var array
     */
    private static $_accessibleProperties = [];

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
     * @param array  $arguments Arguments passed to the function
     * @return mixed
     * @throws MethodNotFoundException
     */
    public function handleMagicMethods($name, $arguments)
    {
        $flag = false;

        if (in_array($type = substr($name, 0, 3), ['set', 'get'])) {
            $property = lcfirst(substr($name, 3));

            if ($this->isPropertyAccessible($property)) {
                switch ($type) {
                    case 'set':
                        $this->{$property} = current($arguments);
                        $flag = true;
                        break;
                    case 'get':
                        return $this->{$property};
                }
            }
        }

        if (false === $flag) {
            throw new MethodNotFoundException(
                'The method "' . $name . '" does not exist in the class "' . get_class($this) . '".',
                1471043854
            );
        }

        return null;
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
        if (false === isset(self::$_accessibleProperties[get_class($this)])) {
            self::$_accessibleProperties[get_class($this)] = [];

            $reflect = new \ReflectionObject($this);
            $properties = $reflect->getProperties(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED);

            foreach ($properties as $property) {
                self::$_accessibleProperties[get_class($this)][$property->getName()] = true;
            }
        }

        return (isset(self::$_accessibleProperties[get_class($this)][$propertyName]));
    }
}
