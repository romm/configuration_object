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
use Romm\ConfigurationObject\Exceptions\EntryNotFoundException;

/**
 * Use this trait in your configuration objects (it will work only if they do
 * use the service `ParentsService`).
 *
 * It will store the parent objects of the current object.
 */
trait ParentsTrait
{

    /**
     * Note: must be private, or the TYPO3 reflection services will go in an
     * infinite loop.
     *
     * @var object[]
     */
    private $_parents = [];

    /**
     * @param object[] $parents
     */
    public function setParents(array $parents)
    {
        $this->_parents = $parents;
    }

    /**
     * Will fetch the first parent which matches the given class name.
     *
     * If a parent is found, then `$callback` is called, and its returned value
     * is returned by this function.
     *
     * If no parent is found, then `$notFoundCallBack` is called if it was
     * defined.
     *
     * @param string   $parentClassName  Name of the class name of the wanted parent.
     * @param callable $callBack         A closure which will be called if the parent is found.
     * @param callable $notFoundCallBack A closure which is called if the parent is not found.
     * @return mixed|null
     */
    public function withFirstParent($parentClassName, callable $callBack, callable $notFoundCallBack = null)
    {
        // We first check if the registered parents do match the wanted parent.
        foreach ($this->_parents as $parent) {
            if ($parent instanceof $parentClassName) {
                return $callBack($parent);
            }
        }

        // Then, we check each parent's parents.
        foreach ($this->_parents as $parent) {
            if (Core::get()->getParentsUtility()->classUsesParentsTrait($parent)) {
                /** @var ParentsTrait $parent */
                return $parent->withFirstParent($parentClassName, $callBack, $notFoundCallBack);
            }
        }

        return (null !== $notFoundCallBack)
            ? $notFoundCallBack()
            : null;
    }

    /**
     * Returns true if the class has a given parent.
     *
     * @param string $parentClassName Name of the parent class.
     * @return bool
     */
    public function hasParent($parentClassName)
    {
        foreach ($this->_parents as $parent) {
            if ($parent instanceof $parentClassName) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the first found instance of the desired parent. Returns null if
     * the parent was not found.
     *
     * It is advised to use the function `hasParent()` before using this
     * function.
     *
     * @param string $parentClassName Name of the parent class.
     * @return object
     * @throws EntryNotFoundException
     */
    public function getFirstParent($parentClassName)
    {
        foreach ($this->_parents as $parent) {
            if ($parent instanceof $parentClassName) {
                return $parent;
            }
        }

        throw new EntryNotFoundException(
            'The parent "' . $parentClassName . '" was not found in this object (class "' . get_class($this) . '"). Use the function "hasParent()" before your call to this function!',
            1471379635
        );
    }
}
