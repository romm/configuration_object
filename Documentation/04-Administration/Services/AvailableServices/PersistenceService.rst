.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt

|newpage|

.. _administration-service-persistenceService:

Persistence service
===================

This service will handle all the persistence related-features, meaning it will allow configuration objects to have domain objects attributes (for instance :php:`TYPO3\CMS\Extbase\Domain\Model\FrontendUser` or :php:`TYPO3\CMS\Extbase\Domain\Model\Category`). Basically, every property which is typed as a class which implements the interface :php:`TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface` will be handled correctly.

It means you can fill a configuration array with identifiers (generally the value of ``uid``), and the API will automatically fetch the correct domain object during the conversion of the configuration object. Look below for a working example.

.. note::

    This service can be used when the :ref:`administration-service-cacheService` is activated, it will always fetch an updated version of the domain objects, and not put them directly in cache.

Usage
-----

You can activate this service for a given configuration object by attaching it to the ``ServiceFactory`` in the static function ``getConfigurationObjectServices()``. Use the constant :php:`ServiceInterface::SERVICE_PERSISTENCE` as an identifier for this service (see example below).

Example
-------

.. code-block:: php
    :linenos:
    :emphasize-lines: 13,18,28,34,37-38

    use Romm\ConfObj\Service\ServiceInterface;
    use Romm\ConfObj\Service\ServiceFactory;
    use Romm\ConfObj\ConfigurationObjectInterface;
    use Romm\ConfObj\Traits\ConfigurationObject\DefaultConfigurationObjectTrait;
    use Romm\ConfObj\Traits\ConfigurationObject\MagicMethodsTrait;

    class Company implements ConfigurationObjectInterface
    {
        use DefaultConfigurationObjectTrait;
        use MagicMethodsTrait;

        /**
         * @var \TYPO3\CMS\Beuser\Domain\Model\BackendUser
         */
        protected $boss;

        /**
         * @var \ArrayObject<\TYPO3\CMS\Beuser\Domain\Model\BackendUser>
         */
        protected $employees;

        /**
         * @return ServiceFactory
         */
        public static function getConfigurationObjectServices()
        {
            return ServiceFactory::getInstance()
                ->attach(ServiceInterface::SERVICE_PERSISTENCE);
        }
    }

    $companyConfigurationArray = [
        // Identifier of the `backend_users` record for the boss user account.
        'boss'      => 1,
        'employees' => [
            // Identifiers of the `backend_users` records for every employee.
            'john.doe' => 2,
            'jane.doe' => 3
        ]
    ];

    $myCompany = ConfigurationObjectFactory::get(
        Company::class,
        $companyConfigurationArray
    );