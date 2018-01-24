<?php
/*
 * 2018 Romain CANON <romain.hydrocanon@gmail.com>
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
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ParentsRecursiveService implements SingletonInterface
{
    /**
     * @var ParentsRecursiveService
     */
    private static $instance;

    /**
     * @var callable
     */
    protected $callback;

    /**
     * @var object[]
     */
    protected $processedParents = [];

    /**
     * @return ParentsRecursiveService
     */
    public static function get()
    {
        if (null === self::$instance) {
            self::$instance = GeneralUtility::makeInstance(static::class);
        }

        return self::$instance;
    }

    /**
     * Will loop along each parent of the given root object, and every parent of
     * the parents: the given callback is called with a single parameter which
     * is the current parent.
     *
     * When the callback returns `false`, the loop breaks.
     *
     * @param callable     $callback
     * @param ParentsTrait $rootObject
     * @param object[]     $parents
     */
    public function alongParents(callable $callback, $rootObject, array $parents)
    {
        $flag = false;

        if (null === $this->callback) {
            $flag = true;

            $this->callback = $callback;
            $this->processedParents = [$rootObject];
        }

        $this->alongParentsInternal($callback, $parents);

        if (true === $flag) {
            $this->callback = null;
            $this->processedParents = [];
        }
    }

    /**
     * @param callable $callback
     * @param array    $parents
     */
    protected function alongParentsInternal(callable $callback, array $parents)
    {
        foreach ($parents as $parent) {
            if (false === $this->parentWasProcessed($parent)) {
                $this->processedParents[] = $parent;

                $result = call_user_func($callback, $parent);

                if (false === $result) {
                    break;
                }

                if (Core::get()->getParentsUtility()->classUsesParentsTrait($parent)) {
                    /** @var ParentsTrait $parent */
                    $parent->alongParents($callback);
                }
            }
        }
    }

    /**
     * @param object $parent
     * @return bool
     */
    protected function parentWasProcessed($parent)
    {
        foreach ($this->processedParents as $processedParent) {
            if ($processedParent === $parent) {
                return true;
            }
        }

        return false;
    }
}
