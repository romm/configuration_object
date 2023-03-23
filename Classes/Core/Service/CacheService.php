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

use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class CacheService implements SingletonInterface
{
    const CACHE_IDENTIFIER = 'configuration_object';
    const CACHE_TAG_DYNAMIC_CACHE = 'dynamic-cache';

    /**
     * @var CacheManager
     */
    protected $cacheManager;

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
