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

namespace Romm\ConfigurationObject\Service;

use Romm\ConfigurationObject\Exceptions\InvalidServiceOptionsException;
use Romm\ConfigurationObject\Exceptions\InvalidTypeException;
use Romm\ConfigurationObject\Service\DataTransferObject\AbstractServiceDTO;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

/**
 * This class should be inherited by every configuration object service.
 *
 * Here, the service will be properly initialized.
 */
abstract class AbstractService implements ServiceInterface
{

    /**
     * This is the list of supported options for the children class.
     *
     * Key is the option name, and value is an array in which:
     * - the first key is the default value;
     * - the second key is a boolean: true means the option is required.
     *
     * Example:
     * $supportedOptions = [
     *      'cacheName' => ['myCacheKey', true]
     * ];
     *
     * @var array
     */
    protected $supportedOptions = [];

    /**
     * Contains the options sent to this service. Filters with options given in
     * `$supportedOptions`.
     *
     * @var array
     */
    protected $options = [];

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var callable[]
     */
    protected static $delayedCallbacks = [];

    /**
     * Initializes the service: options are checked and added to the class
     * properties.
     *
     * @inheritdoc
     */
    final public function initializeObject(array $options = [])
    {
        $this->checkUnknownOptions($options);
        $this->checkRequiredOptions($options);
        $this->options = $this->fillOptionsWithValues($options);
    }

    /**
     * Checks if an unknown option exists in the given options array.
     *
     * @param array $options
     * @throws InvalidServiceOptionsException
     */
    protected function checkUnknownOptions(array $options)
    {
        $unsupportedOptions = array_diff_key($options, $this->supportedOptions);

        if ([] !== ($unsupportedOptions)) {
            throw new InvalidServiceOptionsException(
                'Unsupported validation option(s) found: ' . implode(', ', array_keys($unsupportedOptions)),
                1456397655
            );
        }
    }

    /**
     * Will check if the required options are correctly filled.
     *
     * @param array $options
     * @throws InvalidServiceOptionsException
     */
    protected function checkRequiredOptions(array $options)
    {
        array_walk(
            $this->supportedOptions,
            function ($supportedOptionData, $supportedOptionName, $options) {
                if (isset($supportedOptionData[1])
                    && true === $supportedOptionData[1]
                    && empty($supportedOptionData[0])
                    && !array_key_exists($supportedOptionName, $options)
                ) {
                    throw new InvalidServiceOptionsException(
                        'Required validation option not set: ' . $supportedOptionName,
                        1456397839
                    );
                }
            },
            $options
        );
    }

    /**
     * Will fill the options of this service with the given values.
     *
     * @param array $options
     * @return array
     * @throws InvalidTypeException
     */
    protected function fillOptionsWithValues(array $options)
    {
        return array_merge(
            array_map(
                function ($value) {
                    if (false === is_array($value)) {
                        throw new InvalidTypeException(
                            'Supported options of a service must be an array with a value as first entry, and a flag to tell if the option is required in the second entry.',
                            1459249834
                        );
                    }

                    return $value[0];
                },
                $this->supportedOptions
            ),
            $options
        );
    }

    /**
     * @inheritdoc
     */
    public function initialize()
    {
        // Should be implemented in children classes.
    }

    /**
     * Use this function to delay a certain part of the script during an event
     * dispatch. This allows to run parts of the script in a sorted way, based
     * on a priority value.
     *
     * The higher the priority is, the faster the script will run.
     *
     * Please note that the usage of this function is not needed in every event.
     *
     * @param int      $priority Priority: the higher it is, the faster the script will run.
     * @param callable $callback Function which will be called later.
     * @throws InvalidTypeException
     */
    protected function delay($priority, callable $callback)
    {
        if (false === MathUtility::canBeInterpretedAsInteger($priority)) {
            throw new InvalidTypeException(
                'The priority must be an integer, ' . gettype($priority) . ' was given.',
                1457014282
            );
        }
        if (false === isset(static::$delayedCallbacks[$priority])) {
            static::$delayedCallbacks[$priority] = [];
        }

        static::$delayedCallbacks[$priority][] = $callback;
    }

    /**
     * Will run every delayed callback which was registered during an event.
     *
     * @see      \Romm\ConfigurationObject\Service\AbstractService::delay()
     *
     * @param AbstractServiceDTO $dto The data transfer object sent to the services.
     * @internal This function is reserved for internal usage only, you should not use it in third party applications!
     */
    public function runDelayedCallbacks(AbstractServiceDTO $dto)
    {
        krsort(static::$delayedCallbacks);

        foreach (static::$delayedCallbacks as $callbacks) {
            foreach ($callbacks as $callback) {
                $callback($dto);
            }
        }

        static::$delayedCallbacks = [];
    }

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function injectObjectManager(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }
}
