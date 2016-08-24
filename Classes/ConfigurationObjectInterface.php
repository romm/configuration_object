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

namespace Romm\ConfigurationObject;

use Romm\ConfigurationObject\Service\ServiceFactory;

/**
 * This interface must be implemented by every class which may be used as a
 * configuration object.
 *
 * It is also advised for this class to use the default trait provided by this
 * extension (`DefaultConfigurationObjectTrait`), which implements the needed
 * functions for the object to run correctly.
 */
interface ConfigurationObjectInterface
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
    public static function getConfigurationObjectServices();
}
