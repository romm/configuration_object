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

namespace Romm\ConfigurationObject\Service\Items\MixedTypes;

/**
 * Use this interface if a node of your configuration object may use different
 * types of classes.
 *
 * Example:
 *
 * Imagine a property `$animal` of your object which can contain either an
 * instance of `Cat` or `Dog`. These classes should then inherit an abstract
 * class like `AbstractAnimal`. Then, the type given to the `@var` annotation of
 * your property would be `AbstractAnimal`.
 *
 * This abstract class must have its own implementation of the static function
 * `getInstanceClassName()`, which will be used to determine the real type of
 * the data when it will be converted.
 */
interface MixedTypesInterface
{

    /**
     * This function is called when the current configuration object implements
     * this interface.
     *
     * In this case, the function must be extended by your class, and be able to
     * return a valid class name, depending on the values in `$data`.
     *
     * For example, the data variable can contain a property `type`, which will
     * contain useful information to detect to which class it refers to. For
     * instance, if type equals "cat", the name of the class `Cat` is returned.
     * However, if type equals "dog", the name of the class `Dog` is returned.
     *
     * @param MixedTypesResolver $resolver Resolver used to fetch the real type of the object.
     */
    public static function getInstanceClassName(MixedTypesResolver $resolver);
}
