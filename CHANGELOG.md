# ![Configuration Object](Documentation/Images/configuration-object-icon@medium.png) Configuration Object – ChangeLog

1.2.0 - 2016-10-04
------------------

[[37278f6](https://github.com/romm/configuration_object/commit/37278f690537d371467b61ee1eb79db29f779fa8)][FEATURE] Allow to initialize services for external unit tests

The trait `UnitTestUtility` has been renamed to `ConfigurationObjectUnitTestUtility`. This trait should be used in external tests classes which need to use configuration objects.

These classes must implement the function `setUp()` and call the function `initializeConfigurationObjectTestServices()`.

See documentation for more details.

1.1.0 - 2016-09-08
------------------

Minor release which adds one small feature for `Romm\ConfigurationObject\Traits\ConfigurationObject\MagicMethodsTrait`: possibility to use getters and setters for UpperCamelCase properties.
