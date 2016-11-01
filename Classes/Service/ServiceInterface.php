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

use Romm\ConfigurationObject\Service\Items\Cache\CacheService;
use Romm\ConfigurationObject\Service\Items\DataPreProcessor\DataPreProcessorService;
use Romm\ConfigurationObject\Service\Items\MixedTypes\MixedTypesService;
use Romm\ConfigurationObject\Service\Items\Parents\ParentsService;
use Romm\ConfigurationObject\Service\Items\Persistence\PersistenceService;
use Romm\ConfigurationObject\Service\Items\StoreConfigurationArray\StoreConfigurationArrayService;

/**
 * Interface for any configuration object service.
 */
interface ServiceInterface
{

    /**
     * @see \Romm\ConfigurationObject\Service\Items\Cache\CacheService
     */
    const SERVICE_CACHE = CacheService::class;

    /**
     * @see \Romm\ConfigurationObject\Service\Items\Persistence\PersistenceService
     */
    const SERVICE_PERSISTENCE = PersistenceService::class;

    /**
     * @see \Romm\ConfigurationObject\Service\Items\Parents\ParentsService
     */
    const SERVICE_PARENTS = ParentsService::class;

    /**
     * @see \Romm\ConfigurationObject\Service\Items\StoreConfigurationArray\StoreConfigurationArrayService
     */
    const SERVICE_STORE_CONFIGURATION_ARRAY = StoreConfigurationArrayService::class;

    /**
     * @see \Romm\ConfigurationObject\Service\Items\DataPreProcessor\DataPreProcessorService
     */
    const SERVICE_DATA_PRE_PROCESSOR = DataPreProcessorService::class;

    /**
     * @see \Romm\ConfigurationObject\Service\Items\MixedTypes\MixedTypesService
     */
    const SERVICE_MIXED_TYPES = MixedTypesService::class;

    /**
     * Initializes the service.
     */
    public function initialize();

    /**
     * This function is used to inject class variables, and handle the service
     * options.
     *
     * @param array $options Options sent to the service.
     */
    public function initializeObject(array $options = []);
}
