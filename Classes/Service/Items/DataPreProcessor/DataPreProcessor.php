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

namespace Romm\ConfigurationObject\Service\Items\DataPreProcessor;

use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Error\Result;

/**
 * An instance of this class is given to the following function when it is
 * called: `DataPreProcessorInterface::dataPreProcessor()`.
 *
 * You can use it to customize the data which will be converted in an object
 * just after. You can:
 *
 * - Get the current data with the function `getData()`;
 * - Save the customized data with the function `setData()`;
 * - Add an error if somehow, the data is invalid, with the function
 *   `addError()`.
 */
class DataPreProcessor
{

    /**
     * @var mixed
     */
    protected $data;

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
     * @param Error $error
     */
    public function addError(Error $error)
    {
        $this->result->addError($error);
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
     * @return Result
     */
    public function getResult()
    {
        return $this->result;
    }
}
