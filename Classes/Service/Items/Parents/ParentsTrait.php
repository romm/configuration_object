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

use Romm\ConfigurationObject\Exceptions\DuplicateEntryException;
use Romm\ConfigurationObject\Exceptions\EntryNotFoundException;
use Romm\ConfigurationObject\Exceptions\InvalidTypeException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
     *
     * @deprecated This function is deprecated and will be removed in v2!
     *             Use function `addParents()` instead.
     */
    public function setParents(array $parents)
    {
        GeneralUtility::logDeprecatedFunction();

        $this->_parents = $parents;
    }

    /**
     * @param object $parent
     * @param bool   $direct If true, the parent will be added as the direct (closest) parent of this object.
     * @return $this
     * @throws DuplicateEntryException
     * @throws InvalidTypeException
     */
    public function attachParent($parent, $direct = true)
    {
        if (false === is_object($parent)) {
            throw new InvalidTypeException(
                'The parent must be an object, "' . gettype($parent) . '" was given.',
                1493804124
            );
        }

        foreach ($this->_parents as $parentItem) {
            if ($parent === $parentItem) {
                throw new DuplicateEntryException(
                    'The given parent (' . get_class($parent) . ') was already attached to this object (' . get_class($this) . ').',
                    1493804518
                );
            }
        }

        if (true === $direct) {
            array_unshift($this->_parents, $parent);
        } else {
            array_push($this->_parents, $parent);
        }

        return $this;
    }

    /**
     * Loops on each given parent and attach it to this object.
     *
     * The order matters: the first item will be added as a direct parent
     * whereas the last one will be the remote parent.
     *
     * Note that this function will also reset
     *
     * @param object[] $parents
     */
    public function attachParents(array $parents)
    {
        $this->_parents = [];

        foreach ($parents as $parent) {
            $this->attachParent($parent, false);
        }
    }

    /**
     * Will loop along each parent of this object, and every parent of the
     * parents: the given callback is called with a single parameter which is
     * the current parent.
     *
     * When the callback returns `false`, the loop breaks.
     *
     * @param callable $callback
     */
    public function alongParents(callable $callback)
    {
        ParentsRecursiveService::get()->alongParents($callback, $this, $this->_parents);
    }

    /**
     * Returns true if the class has a given parent.
     *
     * @param string $parentClassName Name of the parent class.
     * @return bool
     */
    public function hasParent($parentClassName)
    {
        $found = false;

        $this->alongParents(function ($parent) use ($parentClassName, &$found) {
            if ($parent instanceof $parentClassName) {
                $found = true;

                return false;
            }

            return true;
        });

        return $found;
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
     * @param callable $callback         A closure which will be called if the parent is found.
     * @param callable $notFoundCallback A closure which is called if the parent is not found.
     * @return mixed|null
     */
    public function withFirstParent($parentClassName, callable $callback, callable $notFoundCallback = null)
    {
        $result = null;

        if ($this->hasParent($parentClassName)) {
            $parent = $this->getFirstParent($parentClassName);
            $result = call_user_func($callback, $parent);
        } elseif (null !== $notFoundCallback) {
            $result = call_user_func($notFoundCallback);
        }

        return $result;
    }

    /**
     * Returns the first found instance of the desired parent.
     *
     * An exception is thrown if the parent is not found. It is advised to use
     * the function `hasParent()` before using this function.
     *
     * @param string $parentClassName Name of the parent class.
     * @return object
     * @throws EntryNotFoundException
     */
    public function getFirstParent($parentClassName)
    {
        $foundParent = null;

        $this->alongParents(function ($parent) use ($parentClassName, &$foundParent) {
            if ($parent instanceof $parentClassName) {
                $foundParent = $parent;

                return false;
            }

            return true;
        });

        if (null === $foundParent) {
            throw new EntryNotFoundException(
                'The parent "' . $parentClassName . '" was not found in this object (class "' . get_class($this) . '"). Use the function "hasParent()" before your call to this function!',
                1471379635
            );
        }

        return $foundParent;
    }
}
