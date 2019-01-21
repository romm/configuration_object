<?php
if (!defined('TYPO3_MODE')) {
    throw new \Exception('Access denied.');
}

call_user_func(
    function () {
        if (class_exists(\Doctrine\Common\Annotations\AnnotationReader::class)) {
            \Doctrine\Common\Annotations\AnnotationReader::addGlobalIgnoredName('mixedTypesResolver');
            \Doctrine\Common\Annotations\AnnotationReader::addGlobalIgnoredName('disableMagicMethods');
        }

        /** @var \Romm\ConfigurationObject\Core\Service\CacheService $cacheService */
        $cacheService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Romm\ConfigurationObject\Core\Service\CacheService::class);
        $cacheService->registerInternalCache();
        $cacheService->registerDynamicCaches();
    }
);
