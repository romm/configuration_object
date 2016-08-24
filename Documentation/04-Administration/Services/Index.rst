.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt

.. _administration-services:

Services
========

A service will affect the way a configuration object is built, by applying **different behaviours on the object during its conversion**.

For instance, one of the most important is the ``CacheService``: it will use the TYPO3 caching framework to store entire configuration objects in cache, and fetch it later, improving performances dramatically.

Services may use **parameters**, which you can customize to follow your needs (for instance the names of the cache entries for two different configuration object types can differ).

-----

Attaching services to a configuration object
--------------------------------------------

To attach services to a configuration object, you need to implement the :php:`public static function getConfigurationObjectServices` in the root class of your configuration object. This function must return an instance of :php:`ServiceFactory`.

Using a ``ServiceFactory`` instance, you can add whatever services you need, and even change options for those who can. You can find below the available functions for a ``ServiceFactory`` instance:

=========================================================== ================================================================================================
Function                                                    Description
=========================================================== ================================================================================================
:php:`ServiceFactory::getInstance()`                        Returns a new instance of ``ServiceFactory``.

:php:`$serviceFactory->attach($className, array $options)`  | Activates the given service with the given options (optional). The first parameter is the class
                                                              name of the service.
                                                            |
                                                            | If you intend to use a service from the core of this extension, please use the ``SERVICE_*``
                                                              constants of :php:`ServiceInterface`.
                                                            |
                                                            | When a service is added, the **factory current service pointer** is set on the added service (see
                                                              the function :php:`with()`).

:php:`$serviceFactory->has($className)`                     Returns true if the given service is added to the factory.

:php:`$serviceFactory->with($className)`                    Resets the **factory current service pointer** which can then be used with the function
                                                            :php:`setOption()` (see below).

:php:`$serviceFactory->setOption($name, $value)`            | Sets the value of the given option, for the **factory current service pointer** (last added
                                                              service or last service given to the function :php:`with()`).
                                                            |
                                                            | Options are defined by services themselves, see their documentation to know which options you
                                                              can use.

:php:`$serviceFactory->getOption($name)`                    Returns the current value of the given option, if it is found.
=========================================================== ================================================================================================

**Example:**

.. code-block:: php

    class MyObject implements ConfigurationObjectInterface
    {
        use DefaultConfigurationObjectTrait;

        const CACHE_NAME = 'foo_object';

        /**
         * @return ServiceFactory
         */
        public static function getConfigurationObjectServices()
        {
            return ServiceFactory::getInstance()
                ->attach(ServiceInterface::SERVICE_CACHE)
                ->setOption(CacheService::OPTION_CACHE_NAME, self::CACHE_NAME)
                ->attach(ServiceInterface::SERVICE_PARENTS);
        }
    }

-----

Services list
-------------

Below is the list of all the services provided by this API:

- :ref:`administration-service-cacheService`

  Will automatically manage to save objects in cache, and fetch them later.

- :ref:`administration-service-parentsService`

  Will keep a trace between objects and their sub-objects, which will then be able to fetch their parents.

- :ref:`administration-service-persistenceService`

  Allows the usage of objects which can be accessed with Extbase ``PersistenceManager``, for instance ``Category``, ``FrontendUser``, and basically any object which implements the interface :php:`TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface`.

- :ref:`administration-service-dataPreProcessorService`

  Allows modifying the data used to create an object, just before its creation.

- :ref:`administration-service-mixedTypesService`

  Allows to dynamically change the type of the object which will be created.

- :ref:`administration-service-storeConfigurationArrayService`

  Will store the initial array used to create an object.

.. toctree::
    :titlesonly:
    :hidden:

    AvailableServices/CacheService
    AvailableServices/ParentsService
    AvailableServices/PersistenceService
    AvailableServices/DataPreProcessorService
    AvailableServices/MixedTypesService
    AvailableServices/StoreConfigurationArrayService

-----

Creating your own service
-------------------------

To be written. :-)