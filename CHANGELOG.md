# ![Configuration Object](Documentation/Images/configuration-object-icon@medium.png) Configuration Object – ChangeLog

1.2.3 - 2017-01-30
------------------

1.2.2 - 2016-12-17
------------------

This release introduces some code related features ([PHP Coding Standards Fixer](http://cs.sensiolabs.org/) and [StyleCI](https://styleci.io/)), as well as compatibility for unit tests running on a TYPO3 v6.2+.

- **[[#3](https://github.com/romm/configuration_object/pull/3)] [TASK] Make tests available for TYPO3 6.2**

- **[[f0e3ae5](https://github.com/romm/configuration_object/commit/f0e3ae55a3427e2b85e87da6c1c130c08f2263c8)] [TASK] Add Style-CI badge in README file**

- **[[#2](https://github.com/romm/configuration_object/pull/2)] [CLEANUP] Activate PHP Coding Standards Fixer**

1.2.1 - 2016-10-06
------------------

**[[#1](https://github.com/romm/configuration_object/pull/1)] [TASK] Allow `CacheService` usage in external unit tests**

Fixes the issue that would prevent using configuration objects which use the cache service in external unit tests.

1.2.0 - 2016-10-04
------------------

**[[37278f6](https://github.com/romm/configuration_object/commit/37278f690537d371467b61ee1eb79db29f779fa8)][FEATURE] Allow to initialize services for external unit tests**

The trait `UnitTestUtility` has been renamed to `ConfigurationObjectUnitTestUtility`. This trait should be used in external tests classes which need to use configuration objects.

These classes must implement the function `setUp()` and call the function `initializeConfigurationObjectTestServices()`.

See documentation for more details.

1.1.0 - 2016-09-08
------------------

Minor release which adds one small feature for `Romm\ConfigurationObject\Traits\ConfigurationObject\MagicMethodsTrait`: possibility to use getters and setters for UpperCamelCase properties.
