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

/**
 * You can use this trait in any node of your configuration object. It will be
 * detected by the array converter, which will then store the key of the
 * original array inside the object.
 *
 * Example:
 *
 * $myConfig = [
 *      'animals' => [
 *          'tweety' => [
 *              'type' => 'canary'
 *          ],
 *          'sylvester' => [
 *              'type' => 'cat'
 *          ]
 *      ]
 * ];
 *
 * When converted, the keys `tweety` and `sylvester` will be accessible in the
 * converted objects with the function `getArrayIndex()`.
 *
 */
trait StoreArrayIndexTrait
{

    /**
     * @var string
     */
    private $_arrayIndex;

    /**
     * @return string
     */
    public function getArrayIndex()
    {
        return $this->_arrayIndex;
    }

    /**
     * @param string $arrayIndex
     * @internal
     */
    public function setArrayIndex($arrayIndex)
    {
        $this->_arrayIndex = $arrayIndex;
    }
}
