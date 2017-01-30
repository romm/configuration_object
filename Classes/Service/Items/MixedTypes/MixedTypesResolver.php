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

namespace Romm\ConfigurationObject\Service\Items\MixedTypes;

use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Error\Result;

/**
 * An instance of this class is given to the following function when it is
 * called: `MixedTypesInterface::getInstanceClassName()`.
 *
 * You can use it to customize the type of the object which will be instantiated
 * with the data. You can:
 *
 * - Get the current data with the function `getData()`;
 * - Set the new object type with the function `setObjectType()`;
 * - Add an error if somehow, the data is invalid, with the function
 *   `addError()`.
 */
class MixedTypesResolver
{
    const OBJECT_TYPE_NONE = null;

    /**
     * @var mixed
     */
    protected $data;

    /**
     * @var string
     */
    protected $objectType;

    /**
     * @var Result
     */
    protected $result;

    /**
     * Constructor: initializes a result.
     */
    public function __construct()
    {
        $this->result = new Result();
    }

    /**
     * Adds an error to the processor, which may then be merged with the errors
     * of the property being currently mapped.
     *
     * It will also set the object type to `null`, because if there is an error,
     * the property can probably not being converted correctly.
     *
     * @param Error $error
     */
    public function addError(Error $error)
    {
        $this->result->addError($error);
        $this->setObjectType(self::OBJECT_TYPE_NONE);
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getObjectType()
    {
        return $this->objectType;
    }

    /**
     * @param string $objectType
     */
    public function setObjectType($objectType)
    {
        $this->objectType = $objectType;
    }

    /**
     * @return Result
     */
    public function getResult()
    {
        return $this->result;
    }
}
