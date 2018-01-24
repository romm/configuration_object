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

namespace Romm\ConfigurationObject\Service\Items\DataPreProcessor;

/**
 * Use this interface if you need to manipulate the data array before it is
 * converted in a configuration object. You need to override the static function
 * `dataPreProcessor()` if you implement this interface.
 *
 * Example:
 *
 * You want a simple array with arbitrary keys and values to be stored inside a
 * given property (`$items` for instance) of your configuration object. Then you
 * have to lightly change the data array: in the function `dataPreProcessor()`,
 * you will return a new array with an index `items` containing the first array.
 */
interface DataPreProcessorInterface
{

    /**
     * This function is called when a configuration object implements this
     * interface.
     *
     * It allows you to modify the data which will be injected in the class
     * properties, before the process begins.
     *
     * You may return whatever new data you want.
     *
     * @param DataPreProcessor $processor Processor used to handle data, and possibly add errors.
     */
    public static function dataPreProcessor(DataPreProcessor $processor);
}
