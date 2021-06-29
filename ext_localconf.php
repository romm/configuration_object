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

        if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cache_configuration_object'])) {
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cache_configuration_object'] = [
                'backend'  => \TYPO3\CMS\Core\Cache\Backend\FileBackend::class,
                'frontend' => \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class,
                'groups'   => ['all', 'system']
            ];
        }
        if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cache_configuration_object_default'])) {
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cache_configuration_object_default'] = [
                'backend'  => \TYPO3\CMS\Core\Cache\Backend\FileBackend::class,
                'frontend' => \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class,
                'groups'   => ['all', 'system']
            ];
        }
    }
);
