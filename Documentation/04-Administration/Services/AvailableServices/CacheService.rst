.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt

|newpage|

.. _administration-service-cacheService:

Cache service
=============

Its name speaks for itself: the cache service is an implementation of the TYPO3 caching framework. It will be used to store configuration objects in cache the first time they are created, then fetch the created entry next times. This can improve performances dramatically.

You should **really consider before using this service, as it can lead to unwanted behaviours**. You should obviously use it only for objects which will be needed several times: **do not use it for one-time-run objects**, it would create a cache entry for nothing.

Usage
-----

You can activate this service for a given configuration object by attaching it to the ``ServiceFactory`` in the static function ``getConfigurationObjectServices()``. Use the constant :php:`ServiceInterface::SERVICE_CACHE` as an identifier for this service (see example below).

Options
-------

======================================= =============================================================================================
Name                                    Description
======================================= =============================================================================================
``CacheService::OPTION_CACHE_NAME``     The name of the “*group*” which will contain all the cache entries for this configuration
                                        object.

``CacheService::OPTION_CACHE_BACKEND``  | Type of backend cache used for the cache manager. Default is
                                          :php:`TYPO3\CMS\...\FileBackend`.
                                        |
                                        | See `TYPO3 official documentation`_ for more information.

``CacheService::OPTION_CACHE_GROUPS``   Groups of the cache, default value is ``all``.

``CacheService::OPTION_CACHE_OPTIONS``  Actual options of the cache manager. A good example is the option ``cacheDirectory`` for
                                        the ``FileBackend`` cache. You can find the options of every backend cache in the
                                        TYPO3 official documentation (see above).
======================================= =============================================================================================

.. _TYPO3 official documentation: https://docs.typo3.org/typo3cms/CoreApiReference/CachingFramework/FrontendsBackends/Index.html#cache-backends

Example
-------

.. code-block:: php
    :linenos:
    :emphasize-lines: 29-32

    use Romm\ConfObj\Service\ServiceInterface;
    use Romm\ConfObj\Service\ServiceFactory;
    use Romm\ConfObj\Service\Items\Cache\CacheService;
    use Romm\ConfObj\ConfigurationObjectInterface;
    use Romm\ConfObj\Traits\ConfigurationObject\DefaultConfigurationObjectTrait;
    use TYPO3\CMS\Core\Cache\Backend\MemcachedBackend;

    class MyObject implements ConfigurationObjectInterface
    {
        use DefaultConfigurationObjectTrait;

        const CACHE_NAME = 'foo_object';
        const CACHE_BACKEND = MemcachedBackend::class;

        /**
         * @var array
         */
        private static $cacheOptions = [
            'servers'         => ['my-server.com:1337'],
            'defaultLifetime' => 86400 // 1 day
        ];

        /**
         * @return ServiceFactory
         */
        public static function getConfigurationObjectServices()
        {
            return ServiceFactory::getInstance()
                ->attach(ServiceInterface::SERVICE_CACHE)
                ->setOption(CacheService::OPTION_CACHE_NAME, self::CACHE_NAME)
                ->setOption(CacheService::OPTION_CACHE_BACKEND, self::CACHE_BACKEND)
                ->setOption(CacheService::OPTION_CACHE_OPTIONS, self::$cacheOptions);
        }
    }