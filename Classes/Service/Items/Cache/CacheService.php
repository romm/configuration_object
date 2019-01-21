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

namespace Romm\ConfigurationObject\Service\Items\Cache;

use Romm\ConfigurationObject\ConfigurationObjectInstance;
use Romm\ConfigurationObject\Core\Core;
use Romm\ConfigurationObject\Service\AbstractService;
use Romm\ConfigurationObject\Service\DataTransferObject\GetConfigurationObjectDTO;
use Romm\ConfigurationObject\Service\Event\ConfigurationObjectAfterServiceEventInterface;
use Romm\ConfigurationObject\Service\Event\ConfigurationObjectBeforeServiceEventInterface;
use Romm\ConfigurationObject\Service\Event\ConfigurationObjectBeforeValidationServiceEventInterface;
use Romm\ConfigurationObject\Traits\InternalVariablesTrait;
use TYPO3\CMS\Core\Cache\Backend\FileBackend;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;

/**
 * This service initializes and uses the cache instance used for configuration
 * objects.
 *
 * When an object has been created from scratch, it is stored in a cache
 * instance. Before the mapping is done on the object, this service will check
 * in the cache instance if the object is already there, and return it if it was
 * found.
 *
 * This service will also handle the validation result: instead of refreshing
 * the validation every time the object is created (or fetched from cache), the
 * service checks if at least one property changed, if yes the validation runs
 * again and stores the result in cache.
 */
class CacheService extends AbstractService implements ConfigurationObjectBeforeServiceEventInterface, ConfigurationObjectAfterServiceEventInterface, ConfigurationObjectBeforeValidationServiceEventInterface
{
    use InternalVariablesTrait;

    const OPTION_CACHE_NAME = 'cacheName';
    const OPTION_CACHE_BACKEND = 'backendCache';
    const OPTION_CACHE_GROUPS = 'groups';
    const OPTION_CACHE_OPTIONS = 'options';

    /**
     * Default identifier of the cache instance.
     */
    const DEFAULT_CACHE_IDENTIFIER = 'cache_configuration_object_default';

    /**
     * Default backend cache class name.
     */
    const DEFAULT_CACHE_BACKEND = FileBackend::class;

    /**
     * We need to check very early if the object exists in cache, to avoid
     * calling other services/functions which wont be used because they were
     * already used when the object was put in cache.
     */
    const PRIORITY_CHECK_OBJECT_IN_CACHE = 1000;

    /**
     * We need to save the object in cache after all other scripts manipulated
     * the object for their own needs. Meaning that every service with a
     * priority above this one will be called before, and what they will do may
     * be stored in cache.
     */
    const PRIORITY_SAVE_OBJECT_IN_CACHE = -1000;

    /**
     * The validation result will be refreshed only if it has not been done
     * before, so we leave possibility for other services to do what they want
     * with a lower priority.
     */
    const PRIORITY_REFRESH_VALIDATION_RESULT = -1000;

    /**
     * @inheritdoc
     */
    protected $supportedOptions = [
        self::OPTION_CACHE_NAME    => [self::DEFAULT_CACHE_IDENTIFIER, true],
        self::OPTION_CACHE_BACKEND => [self::DEFAULT_CACHE_BACKEND, true],
        self::OPTION_CACHE_GROUPS  => [['all', 'system'], false],
        self::OPTION_CACHE_OPTIONS => [[], false]
    ];

    /**
     * Identifier of the cache instance.
     *
     * @var string
     */
    protected $cacheIdentifier;

    /**
     * @var array
     */
    protected $configurationObjectsFetchedFromCache = [];

    /**
     * @var CacheManager
     */
    protected static $cacheManager;

    /**
     * @inheritdoc
     */
    public function initialize()
    {
        $this->cacheIdentifier = $this->options[self::OPTION_CACHE_NAME];
        $options = [
            'backend'  => $this->options[self::OPTION_CACHE_BACKEND] ?? null,
            'frontend' => VariableFrontend::class,
            'groups'   => $this->options[self::OPTION_CACHE_GROUPS] ?? [],
            'options'  => $this->options[self::OPTION_CACHE_OPTIONS] ?? []
        ];

        // Adds the cache to the list of TYPO3 caches.
        Core::get()
            ->getCacheService()
            ->registerDynamicCache($this->cacheIdentifier, $options);
    }

    /**
     * Before a configuration object is built, this function will check if the
     * object is already stored in the cache (if true, returns it).
     *
     * @param  GetConfigurationObjectDTO $serviceDataTransferObject
     */
    public function configurationObjectBefore(GetConfigurationObjectDTO $serviceDataTransferObject)
    {
        $this->delay(
            self::PRIORITY_CHECK_OBJECT_IN_CACHE,
            function (GetConfigurationObjectDTO $serviceDataTransferObject) {
                $cacheHash = $this->getConfigurationObjectCacheHash($serviceDataTransferObject);

                if ($this->getCacheInstance()->has($cacheHash)) {
                    $result = $this->getCacheInstance()->get($cacheHash);

                    if ($result instanceof ConfigurationObjectInstance) {
                        $serviceDataTransferObject->setResult($result);

                        $this->configurationObjectsFetchedFromCache[$cacheHash] = true;
                    }
                }
            }
        );
    }

    /**
     * After a configuration object has been built, it is stored in the cache.
     *
     * @param   GetConfigurationObjectDTO $serviceDataTransferObject
     */
    public function configurationObjectAfter(GetConfigurationObjectDTO $serviceDataTransferObject)
    {
        $this->delay(
            self::PRIORITY_SAVE_OBJECT_IN_CACHE,
            function (GetConfigurationObjectDTO $serviceDataTransferObject) {
                $cacheHash = $this->getConfigurationObjectCacheHash($serviceDataTransferObject);

                if (false === isset($this->configurationObjectsFetchedFromCache[$cacheHash])) {
                    $this->getCacheInstance()->set($cacheHash, $serviceDataTransferObject->getResult());
                }
            }
        );
    }

    /**
     * Will handle the cache for the validation result of the configuration
     * object. The following behaviours occur, depending on what was already
     * done:
     *
     * - If the validation result is not stored in cache yet, it is obtained
     *   from the result (no matter if it was already defined or not), and
     *   stored in cache.
     * - Else if the validation result was not previously defined by some other
     *    service, it is obtained from cache and inserted in the result.
     *
     * @param GetConfigurationObjectDTO $serviceDataTransferObject
     */
    public function configurationObjectBeforeValidation(GetConfigurationObjectDTO $serviceDataTransferObject)
    {
        $this->delay(
            self::PRIORITY_REFRESH_VALIDATION_RESULT,
            function (GetConfigurationObjectDTO $serviceDataTransferObject) {
                $objectCacheHash = $this->getConfigurationObjectCacheHash($serviceDataTransferObject);
                $validationCacheHash = $this->getConfigurationObjectValidationResultCacheHash($serviceDataTransferObject);

                if (false === $this->getCacheInstance()->has($validationCacheHash)) {
                    $cacheValidationResult = $serviceDataTransferObject->getResult()->getValidationResult();
                    $this->getCacheInstance()->flushByTag($objectCacheHash);
                    $this->getCacheInstance()->set($validationCacheHash, $cacheValidationResult, [$objectCacheHash]);
                } else {
                    $cacheValidationResult = $this->getCacheInstance()->get($validationCacheHash);
                    $serviceDataTransferObject->getResult()->setValidationResult($cacheValidationResult);
                }
            }
        );
    }

    /**
     * @param GetConfigurationObjectDTO $serviceDataTransferObject
     * @return string
     */
    protected function getConfigurationObjectCacheHash(GetConfigurationObjectDTO $serviceDataTransferObject)
    {
        $className = $serviceDataTransferObject->getConfigurationObjectClassName();
        $data = $serviceDataTransferObject->getConfigurationObjectData();

        return 'object-' . sha1(serialize([$className, $data]));
    }

    /**
     * @param GetConfigurationObjectDTO $serviceDataTransferObject
     * @return string
     */
    protected function getConfigurationObjectValidationResultCacheHash(GetConfigurationObjectDTO $serviceDataTransferObject)
    {
        return 'validation-result-' . sha1(serialize($serviceDataTransferObject->getResult()->getObject(true)));
    }

    /**
     * Returns the cache instance of this service.
     *
     * @return FrontendInterface
     */
    protected function getCacheInstance()
    {
        return $this->getCacheManager()
            ->getCache($this->cacheIdentifier);
    }

    /**
     * @return CacheManager
     */
    public function getCacheManager()
    {
        return Core::get()->getCacheManager();
    }
}
