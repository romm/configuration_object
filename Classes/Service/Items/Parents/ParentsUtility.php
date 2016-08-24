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

namespace Romm\ConfigurationObject\Service\Items\Parents;

use TYPO3\CMS\Core\SingletonInterface;

class ParentsUtility implements SingletonInterface
{

    /**
     * Contains the names of the classes which have already being checked and
     * use the `ParentsTrait`.
     *
     * @var array
     */
    protected $classUsingParentsTrait = [];

    /**
     * Will check and store the class names which use the trait `ParentsTrait`.
     *
     * @param string|object $className
     * @return bool
     */
    public function classUsesParentsTrait($className)
    {
        if (is_object($className)) {
            $className = get_class($className);
        }

        if (false === isset($this->classUsingParentsTrait[$className])) {
            $this->classUsingParentsTrait[$className] = $this->checkClassUsesParentsTrait($className);
        }

        return $this->classUsingParentsTrait[$className];
    }

    /**
     * Will check if the given class name uses the trait `ParentsTrait`.
     *
     * @param string $className
     * @return bool
     */
    protected function checkClassUsesParentsTrait($className)
    {
        $flag = false;
        $classes = array_merge([$className], class_parents($className));

        foreach ($classes as $class) {
            $traits = class_uses($class);
            $flag = $flag || (true === isset($traits[ParentsTrait::class]));
        }

        return $flag;
    }
}
