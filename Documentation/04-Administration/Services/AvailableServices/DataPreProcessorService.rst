.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt

|newpage|

.. _administration-service-dataPreProcessorService:

Data pre-processor service
==========================

Gives the possibility to **modify the data** used to create an object, just **before it is converted**.

For instance, you can use this feature to dynamically change the value of an input by the result of an external service.

Usage
-----

You can activate this service for a given configuration object by attaching it to the ``ServiceFactory`` in the static function ``getConfigurationObjectServices()``. Use the constant :php:`ServiceInterface::SERVICE_DATA_PRE_PROCESSOR` as an identifier for this service (see example below).

You then have to implement the interface :php:`DataPreProcessorInterface` in every class which needs this feature, and write your own :php:`public static function dataPreProcessor(DataPreProcessor $data)` implementation.

.. attention::

    Please be aware that this feature will **not run more than once on configuration objects which use the** ``CacheService``. It means that in this case you **should not** use any function which **can return dynamic values** in here, as it will be called only once.

Example
-------

.. code-block:: php
    :linenos:
    :emphasize-lines: 8,31,41-52

    use Romm\ConfObj\ConfigurationObjectInterface;
    use Romm\ConfObj\Service\ServiceFactory;
    use Romm\ConfObj\Traits\ConfigurationObject\DefaultConfigurationObjectTrait;
    use Romm\ConfObj\Service\ServiceInterface;
    use Romm\ConfObj\Service\Items\DataPreProcessorInterface;
    use Romm\ConfObj\Service\Items\DataPreProcessor;

    class Person implements ConfigurationObjectInterface, DataPreProcessorInterface
    {

        use DefaultConfigurationObjectTrait;

        const AUTO_CITY = 'auto';

        /**
         * @var string
         */
        protected $city;

        /**
         * @var string
         */
        protected $zipCode;

        /**
         * @return ServiceFactory
         */
        public static function getConfigurationObjectServices()
        {
            return ServiceFactory::getInstance()
                ->attach(ServiceInterface::SERVICE_DATA_PRE_PROCESSOR);
        }

        /**
         * Will check if the value of `$data['city']` is set to `auto`. If yes,
         * an external service is used to fetch the city automatically from the
         * zip code.
         *
         * @var array $data
         */
        public static function dataPreProcessor(DataPreProcessor $processor)
        {
            $data = $processor->getData();

            if (self::AUTO_CITY === $data['city']) {
                $zipCode = $data['zipCode'];

                $data['city'] = GeographicUtility::fetchCityFromZipCode($zipCode);

                $processor->setData($data);
            }
        }
    }
