.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt

|newpage|

.. _administration-service-storeConfigurationArrayService:

Store configuration array service
=================================

The goal of this service is to allow configuration objects to store the initial array which was actually used to create the object. You will then be able to fetch the array when your configuration object is created.

Usage
-----

You can activate this service for a given configuration object by attaching it to the ``ServiceFactory`` in the static function ``getConfigurationObjectServices()``. Use the constant :php:`ServiceInterface::SERVICE_STORE_CONFIGURATION_ARRAY` as an identifier for this service (see example below).

You then have to import the trait :php:`StoreConfigurationArrayTrait` in every class which needs to store its configuration. This can be sub-objects of your configuration object root. Every class which uses this trait has access to the function ``getConfigurationArray()`` which returns the full array used to create the object.

Example
-------

.. code-block:: php
    :linenos:
    :emphasize-lines: 23,29,44

    use Romm\ConfObj\Service\ServiceInterface;
    use Romm\ConfObj\Service\ServiceFactory;
    use Romm\ConfObj\Service\Items\StoreConfigurationArray\StoreConfigurationArrayTrait;
    use Romm\ConfObj\ConfigurationObjectFactory;
    use Romm\ConfObj\ConfigurationObjectInterface;
    use Romm\ConfObj\Traits\ConfigurationObject\DefaultConfigurationObjectTrait;

    class MyObject implements ConfigurationObjectInterface
    {
        use DefaultConfigurationObjectTrait;

        /**
         * @var \ArrayObject<SubObject>
         */
        protected $subObjects;

        /**
         * @return ServiceFactory
         */
        public static function getConfigurationObjectServices()
        {
            return ServiceFactory::getInstance()
                ->attach(ServiceInterface::SERVICE_STORE_CONFIGURATION_ARRAY);
        }
    }

    class SubObject
    {
        use StoreConfigurationArrayTrait;

        /**
         * @var string
         */
        protected $name;
    }

    $myConfigurationObject = ConfigurationObjectFactory::get(
        MyObject::class,
        $someConfigurationArray
    );

    foreach($myConfigurationObject->getSubObjects() as $subObject) {
        // Getting the array used to create the sub-object.
        $configurationArray = $subObject->getConfigurationArray();
    }