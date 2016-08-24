.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _usage:

Usage
=====

Setting up a configuration object
---------------------------------

Create your model classes
^^^^^^^^^^^^^^^^^^^^^^^^^

First of all, you have to create the skeleton of your configuration object. It means creating the several classes representing your configuration, add their properties, and indicate their types.

Please note that a configuration object root class (first level of the tree) needs to implement the interface :php:`ConfigurationObjectInterface`. To implement directly the functions needed by the interface, you may (*should*) use the trait :php:`DefaultConfigurationObjectTrait`.

**Example:**

.. code-block:: php

    // my_extension/Classes/Configuration/Company.php

    namespace MyVendor\MyExtension\Configuration;

    use Romm\ConfObj\ConfigurationObjectInterface;
    use Romm\ConfObj\Traits\ConfigurationObject\DefaultConfigurationObjectTrait;

    class Company implements ConfigurationObjectInterface
    {
        use DefaultConfigurationObjectTrait;

        /**
         * @var string
         */
        protected $name;

        /**
         * @var \ArrayObject<MyVendor\MyExtension\Configuration\Employee>
         */
        protected $employees;
    }

|newpage|

.. code-block:: php

    // my_extension/Classes/Configuration/Employee.php

    namespace MyVendor\MyExtension\Configuration;

    class Employee
    {
        /**
         * @var string
         */
        protected $name;

        /**
         * @var string
         */
        protected $email;
    }

-----

|newpage|

Get your configuration object
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

You should now be able to get your configuration object. The basic workflow of the configuration object usage is the following:

1. First, the **configuration plain array** is fetched in whatever way. It can be an array created from scratch, but it can also be the conversion result of a **serialized/JSON string**, or come from the **TypoScript tree**.

2. The plain array is sent to the extension API, like in the following code:

   .. code-block:: php

       $myConfigurationObject = \Romm\ConfObj\ConfigurationObjectFactory::get(
           MyVendor\MyExtension\Configuration\Company::class,
           $myConfigurationArray
       );

3. The script above will return an instance of :php:`ConfigurationObjectInstance`. It contains two important things: the actual converted object, and the possible errors which occurred during conversion.

   In most cases, you will want to know if there have been errors, before allowing your script to continue its execution. This way, you can **inform whoever you want of the errors**, and **prevent your script from failing later on** because the configuration was not right.

   You can find a basic usage below:

   .. code-block:: php

       $companyArray = [
           0 => [
               'name'      => 'My Company',
               'employees' => [
                   [
                       'name'  => 'John Doe',
                       'email' => 'john.doe@my-company.com'
                   ],
                   [
                       'name'  => 'Jane Doe',
                       'email' => 'jane.doe@my-company.com'
                   ]
               ]
           ]
       ];

       $companyObject = \Romm\ConfObj\ConfigurationObjectFactory::get(
           \MyVendor\MyExtension\Configuration\Company::class,
           $companyArray
       );

       $validationResult = $companyObject->getValidationResult();
       if ($validationResult->hasErrors()) {
           $errors = $validationResult->getFlattenedErrors();

           /*
            * Here you can do whatever you want, like sending a mail to someone,
            * or listing the errors in your template.
            */
       } else {
           // Getting the true instance of your converted object.
           $companyObject = $companyObject->getObject();

           // Keep up your script here...
       }

-----

|newpage|

Properties and validation
^^^^^^^^^^^^^^^^^^^^^^^^^

To make sure that the **data array used to build the configuration object is correct**, you can attach **validation rules on properties**. For instance, you may want the name of an employee not to be empty, and its email to be a valid email address.

You can learn how to use the validation API in the chapter “:ref:`administration-validators`”.

Example for the field ``email`` of the class ``Employee``:

.. code-block:: php

    /**
     * @var string
     * @validate NotEmpty
     * @validate EmailAddress
     */
    protected $email;

-----

Attaching services to a configuration object
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

In order to use advanced functionality and/or change a configuration object rendering behaviours, you can activate services. Several services are provided by the extension, with different goals.

You can learn how to use validators, and the list of already available ones in the chapter “:ref:`administration-services`”.

**Example of a service declaration:**

.. code-block:: php

    class Company implements ConfigurationObjectInterface
    {

        const CACHE_NAME = 'company';

        /**
         * @return ServiceFactory
         */
        public static function getConfigurationObjectServices()
        {
            return ServiceFactory::getInstance()
                ->attach(ServiceInterface::SERVICE_CACHE)
                ->setOption(CacheService::OPTION_CACHE_NAME, self::CACHE_NAME);
        }
    }