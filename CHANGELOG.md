# ![Configuration Object](Documentation/Images/configuration-object-icon@medium.png) Configuration Object – ChangeLog

1.5.1 - 2017-03-02
------------------

An issue concerning the cache handling has been solved. Upgrading is recommended!

 - **[[#12](https://github.com/romm/configuration_object/pull/12)] [BUGFIX] Fix incorrect cache handling**

   This commit refactors a major part of how "dynamic" caches are handled by Configuration Object API. In the provided cache service that can be attached to a configuration object, it is possible to declare the options for this cache; it means the cache is registered long after TYPO3 initialization, resulting in issues like caches entries not being deleted on caches flush.

   A new internal cache service has been introduced, which will handle these "dynamic" caches: when one of these caches is used, its configuration is saved in the internal cache: in further request, the cache will be properly registered during TYPO3 initialization.

1.5.0 - 2017-02-27
------------------

A new tag `@disableMagicMethods` has been introduced for classes which use `MagicMethodsTrait`.

 - **[[#10](https://github.com/romm/configuration_object/pull/10)] [FEATURE] Introduce `@disableMagicMethods` tag support**

1.4.0 - 2017-02-21
------------------

One bug has been fixed, and two new (useful 😃) validators have been introduced.

 - **[[f4d59fd](https://github.com/romm/configuration_object/commit/f4d59fd335943d16ef78095aceb45464db4e6de5)] [FEATURE] Introduce `ClassExtendsValidator`**

 - **[[9b2208c](https://github.com/romm/configuration_object/commit/9b2208c2529d64271b7a339169dc7440128d81fe)] [FEATURE] Introduce `ClassImplementsValidator`**

 - **[[150aabf](https://github.com/romm/configuration_object/commit/150aabfbf0b432acf0582f0f9f02fb3a3e62b925)] [BUGFIX] Remove `SingletonInterface` from validator**

1.3.1 - 2017-02-14
------------------

Upgrade PHPUnit version requirement, and replace all `getMock()` calls with `getMockBuilder()`.

- **[[#7](https://github.com/romm/configuration_object/pull/7)] [TASK] Use PHPUnit `MockBuilder`**

1.3.0 - 2017-02-13
------------------

Configuration Object is now compatible with TYPO3 8.5+!

- **[[#5](https://github.com/romm/configuration_object/pull/5)] [FEATURE] Make extension compatible with TYPO3 v8**

1.2.3 - 2017-01-30
------------------

This release fixes one possible bug for external unit testing by libraries using this API.

- **[[0daa837](https://github.com/romm/configuration_object/commit/0daa8370c27f0d180688b7b0140ddd209029b789)] [BUGFIX] Fix possible multiple type converter registration**

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
