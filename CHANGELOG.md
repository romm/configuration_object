# ![Configuration Object](Documentation/Images/configuration-object-icon@medium.png) Configuration Object – ChangeLog

2.0.0 - 2019-01-21
-------------------

 - **[[b2fe328](https://github.com/romm/configuration_object/commit/b2fe328057c8bb6d73d063e61ec361b94f4f530d)] [!!!][TASK] Make extension compatible for TYPO3 9.5**

   Versions 6.2, 7.6 and 8.6 are not supported anymore

 - **[[9ac85e1](https://github.com/romm/configuration_object/commit/9ac85e1455e050e81c97142dbc8a67ee7f8a8a79)] [BUGFIX] Manage recursivity in mapper**

 - **[[297c3c6](https://github.com/romm/configuration_object/commit/297c3c69b865522f79b41e1b51f8876ee0e314ad)] [BUGFIX] Handler invalid cache entry**


1.10.1 - 2018-01-24
-------------------

 - **[[ae026a5](https://github.com/romm/configuration_object/commit/ae026a55bd6501b1127cfb0272d60c992143fda8)] [BUGFIX] Override Extbase reflection service**

   Fixes a severe issue on TYPO3 6.2 instances.
 

1.10.0 - 2017-12-02
-------------------

 - **[[38635d1](https://github.com/romm/configuration_object/commit/38635d13216665f33101523ab125a4c2801dcf20)] [FEATURE] Introduce `FileExistsValidator`**

   This new validator allows checking if a file exists (the syntax `EXT:my_extension/Path/To/My/File.txt` can be used).

 - **[[91caefe](https://github.com/romm/configuration_object/commit/91caefe93626aec280560afb9af9f1213a352725)] [FEATURE] Introduce `IconExistsValidator`**
 
   This new validator allows checking if an icon identifier has been registered in the TYPO3 icon registry.
   
 - **[[bd6e1ff](https://github.com/romm/configuration_object/commit/bd6e1fff590c2e4b6a5343bd0bb72271717a07bb)] [BUGFIX] Check for invalid class name in data pre-processor handler**

 - **[[3fd01de](https://github.com/romm/configuration_object/commit/3fd01de4adbab004149d5d5467793a6b850c07cb)] [BUGFIX] Handle mixed types for properties with single object**

   Object properties that used the "mixed types" feature were not checked if the property was filled with a single instance. Only composite types (means the properties contains several object instances) were actually checked.
   
   This commit patches the validator resolver (in a 💩 way) to allow it to handle single instances. A new internal validator has been introduced: it will wait for the actual object instance to be validated, in order to create an up to date validator conjunction.

 - **[[9250e3e](https://github.com/romm/configuration_object/commit/9250e3e96e1452f1f9258a193d1720e11b0a927e)] [BUGFIX] Handle `null` in source values during mapping**

   If a value that should be mapped to an object is `null`, no type converter will be found and an exception will be thrown. To prevent this, we must handle this specific situation in the mapper, before the type converter is fetched.

1.9.0 - 2017-05-14
------------------

 - **[[2e7838e](https://github.com/romm/configuration_object/commit/2e7838ed782345efbfba30eabc24eb91fde31723)] [FEATURE] Introduce alias method for converting an array to an object**
 
   The static method `ConfigurationObjectFactory::convert()` has been added.
   
   This is a simple alias for: `\Romm\ConfigurationObject\ConfigurationObjectFactory::getInstance()->get(...)`
 
 - **[[2baae53](https://github.com/romm/configuration_object/commit/2baae5336f88fad68ed9780410bd904dd67dfaff)] [FEATURE] Introduce configuration object factory `isRunning` method**
  
   A new method has been added: `ConfigurationObjectFactory::isRunning()`.
   
   With this method, you can check at any moment if the configuration object factory is currently processing (an object is being created). This can be useful for instance if you want to allow magic methods for an object only when it is being converted.
 
 - **[[83de77d](https://github.com/romm/configuration_object/commit/83de77d917416c2271daeeee1b34fa3edd54b2b1)] [FEATURE] Introduce silent exceptions for getter methods**
 
   This commit introduces the support of a new kind of exceptions: "silent exceptions".
   
   This type of exception can be thrown by an object getter methods, and they will be catch during Configuration Object API early processes, while still being thrown when the getter method is actually called from an implementation process.
   
   To make an exception become silent, the class must implement the interface `SilentExceptionInterface`.
   
   See documentation for more information.

1.8.0 - 2017-05-03
------------------

This version mainly affects the `ParentsTrait`, but also improves the errors handling of a configuration object validation result:

 - **[[fef1c9c](https://github.com/romm/configuration_object/commit/fef1c9c99889732a7fc6a93ea4f2b6ea2a242416)] [FEATURE] Introduce `ParentsTrait::alongParents()` function**
 
   This function allows to call a callback function for each parent of an object that uses `ParentsTrait`.
   
   The callback function has a single parameter which is the current parent object. If `false` is returned by the callback, the loop on the parents stops.
   
   This new function is now used by the following functions of `ParentsTrait`: `hasParent`, `withFirstParent` and `getFirstParent`.

 - **[[4c34e94](https://github.com/romm/configuration_object/commit/4c34e94b9df2ab504635a9d2946fda108b873573)] [FEATURE] Introduce parent "attach" functions**
 
   This commit introduces the functions `attachParent()` and `attachParents()` in the `ParentsTrait`. These new functions must be used to attach parents to an object.
   
   These function have more security and flexibility than the `setParents()` function which will be deprecated.

 - **[[d4be3b5](https://github.com/romm/configuration_object/commit/d4be3b512375775cf369a7f42d4504d771d95bcf)] [BUGFIX] Check parents type correctly**
 
   This commit changes how the `ParentsTrait` fetches a parent from a given class name.
   
   For instance, when searching for an interface class, the old way of checking the parents would not have work, now it does.

 - **[[8a05605](https://github.com/romm/configuration_object/commit/8a0560586b094586fdbb8ed18a4ff3aa01e32df6)] [TASK] Return error when required constructor argument is missing**
 
   When an object is built with the object converter, if a required argument for the constructor is missing, it will now return an error instead of throwing an exception.
   
   This way, it will be added to the mapping result, and will let the user have more information about why the object is not valid.

 - **[[a4f41ad](https://github.com/romm/configuration_object/commit/a4f41adac98427bd2592979b9aae4bda11dfb704)] [TASK] Return mapping result if it contains errors**

   When the mapping result given as constructor argument of a configuration object instance contains errors, it will always be returned when the validation result is accessed (function `getValidationResult()`).
   
   This is done because if the mapper did not succeed to build a proper object and its sub-objects, then it makes no sense in many cases to launch a whole validation process on an unfinished object.
   
   This commit leads to more consistency in an object validation result messages.

1.7.0 - 2017-04-06
------------------

TYPO3 8.7 LTS is now officially supported!

1.6.1 - 2017-03-29
------------------

A bug has been fixed:

 - **[[47f8441](https://github.com/romm/configuration_object/commit/47f8441d98612ef2ac0158f6eaa4b71bd55a4c62)] [BUGFIX] Check for interfaces in class check**

1.6.0 - 2017-03-13
------------------

Two new features are introduced:

 - **[[#17](https://github.com/romm/configuration_object/pull/17)] [FEATURE] Introduce support for common array object annotation**

   Previously, when a property would be filled by an array of object, only two annotations were supported:
   
   `\ArrayObject<\Some\Class>` and `array<\Some\Class>`
   
   They are not user-friendly, and most of the time the array is a basic array (not an `ObjectStorage` for instance). The common annotation for this would be:
   
   `\Some\Class[]` - which provides better IDE auto-completion, and has better readability.
   
   This commit introduces support for this annotation, enjoy!
   
 - **[[#19](https://github.com/romm/configuration_object/pull/19)] [FEATURE] Introduce `@mixedTypesResolver` tag annotation**
 
   This tag can be given to properties that need a mixed type resolver to detect their dynamic types. This allows the `@var` tag to be filled with the real type(s) of the values, making the getter/setter methods annotations coherent.

1.5.2 - 2017-03-09
------------------

An issue concerning early cache registration has been solved.

 - **[[#15](https://github.com/romm/configuration_object/pull/15)] [BUGFIX] Allow request to register internal cache very early**
 
   This commit fixes an issue, where a configuration object was constructed very early in TYPO3 request dispatch, for instance in TCA definition. The cache was not registered yet, and that would lead to an exception being thrown.
 
   The cache is now registered any time it is accessed.

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
