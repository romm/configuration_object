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

namespace Romm\ConfigurationObject\Core\Service;

use TYPO3\CMS\Core\Cache\Backend\FileBackend;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class CacheService implements SingletonInterface
{
    const CACHE_IDENTIFIER = 'cache_configuration_object';
    const CACHE_TAG_DYNAMIC_CACHE = 'dynamic-cache';

    /**
     * Options for the internal cache.
     *
     * @var array
     */
    protected $cacheOptions = [
        'backend'  => FileBackend::class,
        'frontend' => VariableFrontend::class,
        'groups'   => ['all', 'system']
    ];

    /**
     * @var CacheManager
     */
    protected $cacheManager;

    /**
     * Function called from `ext_localconf` file which will register the
     * internal cache earlier.
     *
     * @internal
     */
    public function registerInternalCache()
    {
        $cacheManager = $this->getCacheManager();

        if (false === $cacheManager->hasCache(self::CACHE_IDENTIFIER)) {
            $cacheManager->setCacheConfigurations([self::CACHE_IDENTIFIER => $this->cacheOptions]);
        }
    }

    /**
     * This function will take care of initializing all caches that were defined
     * previously  by the `CacheService` which allows dynamic caches to be used
     * for every configuration object type.
     *
     * @see \Romm\ConfigurationObject\Service\Items\Cache\CacheService::initialize()
     * @internal
     */
    public function registerDynamicCaches()
    {
        $dynamicCaches = $this->getCache()->getByTag(self::CACHE_TAG_DYNAMIC_CACHE);

        foreach ($dynamicCaches as $cacheData) {
            $identifier = $cacheData['identifier'];
            $options = $cacheData['options'];

            $this->registerCacheInternal($identifier, $options);
        }
    }

    /**
     * Registers a new dynamic cache: the cache will be added to the cache
     * manager after this function is executed. Its configuration will also be
     * remembered so the cache will be registered properly during the cache
     * framework initialization in further requests.
     *
     * @param string $identifier
     * @param array  $options
     */
    public function registerDynamicCache($identifier, array $options)
    {
        if (false === $this->getCache()->has($identifier)) {
            $this->getCache()->set(
                $identifier,
                [
                    'identifier' => $identifier,
                    'options'    => $options
                ],
                [self::CACHE_TAG_DYNAMIC_CACHE]
            );
        }

        $this->registerCacheInternal($identifier, $options);
    }

    /**
     * @param string $identifier
     * @param array  $options
     */
    protected function registerCacheInternal($identifier, array $options)
    {
        $cacheManager = $this->getCacheManager();

        if (false === $cacheManager->hasCache($identifier)) {
            $cacheManager->setCacheConfigurations([$identifier => $options]);
        }
    }

    /**
     * @return FrontendInterface
     */
    protected function getCache()
    {
        return $this->getCacheManager()->getCache(self::CACHE_IDENTIFIER);
    }

    /**
     * @return CacheManager
     */
    protected function getCacheManager()
    {
        if (null === $this->cacheManager) {
            /** @var CacheManager $cacheManager */
            $this->cacheManager = GeneralUtility::makeInstance(CacheManager::class);
        }

        return $this->cacheManager;
    }
}
