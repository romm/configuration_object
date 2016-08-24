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

use Romm\ConfigurationObject\Service\AbstractService;

/**
 * This service will allow the customization of any data before it is converted
 * to an object inside a configuration object tree.
 *
 * The class which use this service must implement the interface
 * `DataPreProcessorInterface`, and implement the function `dataPreProcessor()`;
 */
class DataPreProcessorService extends AbstractService
{

    /**
     * Default processor which is returned by the function
     * `getDataPreProcessor()` if the given class name does not implement the
     * correct interface.
     *
     * It will prevent potentially thousands of processors created for nothing.
     *
     * @var DataPreProcessor
     */
    protected $defaultProcessor;

    /**
     * Initialization: will create the default processor.
     */
    public function initialize()
    {
        $this->defaultProcessor = new DataPreProcessor();
    }

    /**
     * Will check if the target type class inherits of
     * `DataPreProcessorInterface`. If so, a processor is sent to the function
     * `dataPreProcessor()`, which may modify the data.
     *
     * @param mixed  $data
     * @param string $className Valid class name.
     * @return DataPreProcessor
     */
    public function getDataPreProcessor($data, $className)
    {
        $interfaces = class_implements($className);

        if (true === isset($interfaces[DataPreProcessorInterface::class])) {
            $processor = new DataPreProcessor();
            $processor->setData($data);

            /** @var DataPreProcessorInterface $className */
            $className::dataPreProcessor($processor);
        } else {
            $processor = $this->defaultProcessor;
            $processor->setData($data);
        }

        return $processor;
    }
}
