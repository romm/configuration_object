<?php
namespace Romm\ConfigurationObject\Tests\Unit\Service\Items\Cache;

use Romm\ConfigurationObject\ConfigurationObjectInstance;
use Romm\ConfigurationObject\Service\DataTransferObject\GetConfigurationObjectDTO;
use Romm\ConfigurationObject\Service\Items\Cache\CacheService;
use Romm\ConfigurationObject\Service\ServiceFactory;
use Romm\ConfigurationObject\Tests\Fixture\Model\DummyConfigurationObject;
use Romm\ConfigurationObject\Tests\Unit\ConfigurationObjectUnitTestUtility;
use TYPO3\CMS\Core\Cache\Backend\TransientMemoryBackend;
use TYPO3\CMS\Core\Tests\AccessibleObjectInterface;
use TYPO3\CMS\Core\Tests\UnitTestCase;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Error\Result;

class CacheServiceTest extends UnitTestCase
{

    use ConfigurationObjectUnitTestUtility;

    public function setUp()
    {
        $this->injectCacheManagerInCore();
    }

    /**
     * Will initialize an instance of a cache service, and check that the cache
     * has been correctly registered with the right name.
     *
     * @test
     */
    public function initializationIsSet()
    {
        /** @var CacheService|AccessibleObjectInterface|\PHPUnit_Framework_MockObject_MockObject $mockedCacheService */
        $mockedCacheService = $this->getAccessibleMock(CacheService::class, ['dummy']);
        $cacheManager = $mockedCacheService->getCacheManager();

        $this->assertFalse($cacheManager->hasCache('test'));

        $options = [
            'cacheName' => 'test'
        ];
        $mockedCacheService->_set('options', $options);
        $mockedCacheService->initialize();

        $this->assertTrue($cacheManager->hasCache('test'));

        unset($mockedCacheService);
    }

    /**
     * Will test if an object existing in cache is correctly fetched, so it wont
     * have to be entirely built later.
     *
     * @test
     */
    public function objectExistingInCacheIsFetchedCorrectly()
    {
        $cacheName = __FUNCTION__;
        $serviceFactory = new ServiceFactory();
        $getConfigurationObjectDTO = new GetConfigurationObjectDTO(DummyConfigurationObject::class, $serviceFactory);

        // Dummy object which should be stored in cache.
        $dummyObject = new DummyConfigurationObject();
        $dummyObject->setFoo('foo');
        $dummyObject->setBar('bar');

        $result = new Result();
        $configurationObjectInstance = new ConfigurationObjectInstance($dummyObject, $result);

        // Mocking the cache service so we can inject a custom mocked cache instance.
        /** @var CacheService|AccessibleObjectInterface|\PHPUnit_Framework_MockObject_MockObject $mockedCacheService */
        $mockedCacheService = $this->getAccessibleMock(CacheService::class, ['getCacheInstance']);

        $options = [
            CacheService::OPTION_CACHE_NAME    => $cacheName,
            CacheService::OPTION_CACHE_BACKEND => TransientMemoryBackend::class
        ];
        $mockedCacheService->_set('options', $options);

        $mockedCacheService->initialize();

        $transientMemoryBackendCache = $mockedCacheService->getCacheManager()->getCache($cacheName);

        $mockedCacheService->method('getCacheInstance')
            ->will($this->returnValue($transientMemoryBackendCache));

        // We inject our dummy object in the mocked cache.
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $cacheHash = $mockedCacheService->_call('getConfigurationObjectCacheHash', $getConfigurationObjectDTO);
        $transientMemoryBackendCache->set($cacheHash, $configurationObjectInstance);

        $mockedCacheService->configurationObjectBefore($getConfigurationObjectDTO);
        $mockedCacheService->runDelayedCallbacks($getConfigurationObjectDTO);

        // Checking that the object fetched from cache is still the same dummy object.
        $this->assertEquals(
            serialize($dummyObject),
            serialize($getConfigurationObjectDTO->getResult()->getObject(true))
        );

        unset($serviceFactory);
        unset($getConfigurationObjectDTO);
        unset($dummyObject);
        unset($configurationObjectInstance);
        unset($mockedCacheService);
        unset($transientMemoryBackendCache);
    }

    /**
     * Will check that a new built object is stored in cache.
     *
     * @test
     */
    public function objectIsStoredInCache()
    {
        $cacheName = __FUNCTION__;
        $serviceFactory = new ServiceFactory();
        $getConfigurationObjectDTO = new GetConfigurationObjectDTO(DummyConfigurationObject::class, $serviceFactory);

        // Dummy object which will be stored in cache.
        $dummyObject = new DummyConfigurationObject();
        $dummyObject->setFoo('foo');
        $dummyObject->setBar('bar');

        $result = new Result();
        $configurationObjectInstance = new ConfigurationObjectInstance($dummyObject, $result);

        $getConfigurationObjectDTO->setResult($configurationObjectInstance);

        // Mocking the cache service so we can inject a custom mocked cache instance.
        /** @var CacheService|AccessibleObjectInterface|\PHPUnit_Framework_MockObject_MockObject $mockedCacheService */
        $mockedCacheService = $this->getAccessibleMock(CacheService::class, ['getCacheInstance']);

        $options = [
            CacheService::OPTION_CACHE_NAME    => $cacheName,
            CacheService::OPTION_CACHE_BACKEND => TransientMemoryBackend::class
        ];
        $mockedCacheService->_set('options', $options);

        $mockedCacheService->initialize();

        $transientMemoryBackendCache = $mockedCacheService->getCacheManager()->getCache($cacheName);

        $mockedCacheService->method('getCacheInstance')
            ->will($this->returnValue($transientMemoryBackendCache));

        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $cacheHash = $mockedCacheService->_call('getConfigurationObjectCacheHash', $getConfigurationObjectDTO);

        // Checking that the cache entry does not exist yet.
        $this->assertFalse($transientMemoryBackendCache->has($cacheHash));

        $mockedCacheService->configurationObjectAfter($getConfigurationObjectDTO);
        $mockedCacheService->runDelayedCallbacks($getConfigurationObjectDTO);

        /** @var ConfigurationObjectInstance $cacheEntry */
        $cacheEntry = $transientMemoryBackendCache->get($cacheHash);

        // Now the cache entry should be present.
        $this->assertTrue($transientMemoryBackendCache->has($cacheHash));
        $this->assertEquals(
            serialize($dummyObject),
            serialize($cacheEntry->getObject(true))
        );

        unset($serviceFactory);
        unset($getConfigurationObjectDTO);
        unset($dummyObject);
        unset($configurationObjectInstance);
        unset($mockedCacheService);
        unset($transientMemoryBackendCache);
    }

    /**
     * Will check that the validation result of a configuration object is stored
     * in cache.
     *
     * @test
     */
    public function validationResultIsStoredInCache()
    {
        $cacheName = __FUNCTION__;
        $serviceFactory = new ServiceFactory();
        $getConfigurationObjectDTO = new GetConfigurationObjectDTO(DummyConfigurationObject::class, $serviceFactory);

        // Dummy object which will be stored in cache.
        $dummyObject = new DummyConfigurationObject();
        $dummyObject->setFoo('foo');
        $dummyObject->setBar('bar');

        $mapperResult = new Result();
        $configurationObjectInstance = new ConfigurationObjectInstance($dummyObject, $mapperResult);

        $result = new Result();
        $error = new Error('hello world!', 1337);
        $result->addError($error);
        $configurationObjectInstance->setValidationResult($result);

        $getConfigurationObjectDTO->setResult($configurationObjectInstance);

        // Mocking the cache service so we can inject a custom mocked cache instance.
        /** @var CacheService|AccessibleObjectInterface|\PHPUnit_Framework_MockObject_MockObject $mockedCacheService */
        $mockedCacheService = $this->getAccessibleMock(CacheService::class, ['getCacheInstance']);

        $options = [
            CacheService::OPTION_CACHE_NAME    => $cacheName,
            CacheService::OPTION_CACHE_BACKEND => TransientMemoryBackend::class
        ];
        $mockedCacheService->_set('options', $options);

        $mockedCacheService->initialize();

        $transientMemoryBackendCache = $mockedCacheService->getCacheManager()->getCache($cacheName);

        $mockedCacheService->method('getCacheInstance')
            ->will($this->returnValue($transientMemoryBackendCache));

        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $cacheHash = $mockedCacheService->_call('getConfigurationObjectValidationResultCacheHash', $getConfigurationObjectDTO);

        // Checking that the cache entry does not exist yet.
        $this->assertFalse($transientMemoryBackendCache->has($cacheHash));

        $mockedCacheService->configurationObjectBeforeValidation($getConfigurationObjectDTO);
        $mockedCacheService->runDelayedCallbacks($getConfigurationObjectDTO);

        /** @var Result $cacheEntry */
        $cacheEntry = $transientMemoryBackendCache->get($cacheHash);

        // Now the cache entry should be present.
        $this->assertTrue($transientMemoryBackendCache->has($cacheHash));
        $this->assertEquals(
            serialize($result),
            serialize($cacheEntry)
        );

        /*
         * Now that the entry has been stored in cache, we check that a second
         * call will fetch the result from cache, instead of building it.
         */
        $emptyResult = new Result();
        $getConfigurationObjectDTO->getResult()->setValidationResult($emptyResult);

        $mockedCacheService->configurationObjectBeforeValidation($getConfigurationObjectDTO);
        $mockedCacheService->runDelayedCallbacks($getConfigurationObjectDTO);

        $this->assertEquals(
            serialize($result),
            serialize($getConfigurationObjectDTO->getResult()->getValidationResult())
        );

        unset($serviceFactory);
        unset($getConfigurationObjectDTO);
        unset($dummyObject);
        unset($configurationObjectInstance);
        unset($mockedCacheService);
        unset($transientMemoryBackendCache);
    }
}
