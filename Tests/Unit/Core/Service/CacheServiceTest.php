<?php
namespace Romm\ConfigurationObject\Tests\Unit\Core\Service\Cache;

use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Romm\ConfigurationObject\Core\Service\CacheService;
use Romm\ConfigurationObject\Tests\Unit\AbstractUnitTest;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;

class CacheServiceTest extends AbstractUnitTest
{
    /**
     * Checks that the internal cache used by the API is properly registered
     * during TYPO3 initialization.
     *
     * @test
     */
    public function internalCacheIsRegisteredProperly()
    {
        $cacheManager = new CacheManager;

        /** @var CacheService|\PHPUnit_Framework_MockObject_MockObject $cacheServiceMock */
        $cacheServiceMock = $this->getMockBuilder(CacheService::class)
            ->setMethods(['getCacheManager'])
            ->getMock();

        $cacheServiceMock->method('getCacheManager')
            ->willReturn($cacheManager);

        $this->assertFalse($cacheManager->hasCache(CacheService::CACHE_IDENTIFIER));
        $cacheServiceMock->registerInternalCache();
        $this->assertTrue($cacheManager->hasCache(CacheService::CACHE_IDENTIFIER));
    }

    /**
     * Checks that a cache can be registered dynamically, and only once per
     * request.
     *
     * @test
     */
    public function registerDynamicCacheWorksProperly()
    {
        $cacheIdentifier = 'foo';
        $cacheOptions = ['bar' => 'baz'];

        /** @var CacheService|\PHPUnit_Framework_MockObject_MockObject $cacheServiceMock */
        $cacheServiceMock = $this->getMockBuilder(CacheService::class)
            ->setMethods(['getCache', 'getCacheManager'])
            ->getMock();

        /** @var VariableFrontend|ObjectProphecy $cacheProphecy */
        $cacheProphecy = $this->prophesize(VariableFrontend::class);

        $cacheProphecy->has($cacheIdentifier)
            ->shouldBeCalledTimes(2)
            ->will(function () use ($cacheProphecy, $cacheIdentifier, $cacheOptions) {
                $cacheProphecy->set($cacheIdentifier, Argument::type('array'), [CacheService::CACHE_TAG_DYNAMIC_CACHE])
                    ->shouldBeCalledTimes(1);

                $cacheProphecy->has($cacheIdentifier)
                    ->willReturn(true);

                return false;
            });

        $cacheProphecy->getByTag(CacheService::CACHE_TAG_DYNAMIC_CACHE)
            ->shouldBeCalledTimes(1)
            ->willReturn([
                'foo' => [
                    'identifier' => 'foo',
                    'options'    => []
                ]
            ]);

        $cacheServiceMock->method('getCache')
            ->willReturn($cacheProphecy->reveal());

        /** @var CacheManager|ObjectProphecy $cacheManagerProphecy */
        $cacheManagerProphecy = $this->prophesize(CacheManager::class);

        $cacheManagerProphecy->hasCache($cacheIdentifier)
            ->shouldBeCalledTimes(3)
            ->will(function () use ($cacheManagerProphecy, $cacheIdentifier) {
                $cacheManagerProphecy->hasCache($cacheIdentifier)
                    ->willReturn(true);

                $cacheManagerProphecy->setCacheConfigurations(Argument::type('array'))
                    ->shouldBeCalledTimes(1);

                return false;
            });

        $cacheServiceMock->method('getCacheManager')
            ->willReturn($cacheManagerProphecy->reveal());

        $cacheServiceMock->registerDynamicCache($cacheIdentifier, $cacheOptions);
        $cacheServiceMock->registerDynamicCache($cacheIdentifier, $cacheOptions);
        $cacheServiceMock->registerDynamicCaches();
    }
}
