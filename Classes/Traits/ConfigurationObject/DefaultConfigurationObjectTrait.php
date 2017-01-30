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

use Romm\ConfigurationObject\Service\ServiceFactory;

/**
 * This is a default trait for implementing basic functions which are needed for
 * a configuration object. Generally, every class which implements the interface
 * `Romm\ConfigurationObject\ConfigurationObjectInterface` should use this
 * trait.
 */
trait DefaultConfigurationObjectTrait
{

    /**
     * Override this method in your configuration object to customize which
     * services it will use.
     *
     * Example:
     *
     * return ServiceFactory::getInstance()
     *     ->attach(CacheService::class)
     *     ->setOption(CacheService::OPTION_CACHE_NAME, 'my_custom_cache')
     *     ->attach(MyCustomService::class, ['foo' => 'bar'])
     *     ->setOption('optionForMyCustomService', 'foo');
     *
     * @return ServiceFactory
     */
    public static function getConfigurationObjectServices()
    {
        return ServiceFactory::getInstance();
    }
}
