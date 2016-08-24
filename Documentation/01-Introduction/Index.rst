.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt

What is it?
===========

    .. only:: html

        Transform any configuration plain array into a dynamic and configurable object structure, and pull apart configuration handling from the main logic of your script. Use provided services to add more functionality to your objects: cache, parents, persistence and much more.

Introduction
------------

.. only:: latex

    “*Transform any configuration plain array into a dynamic and configurable object structure, and pull apart configuration handling from the main logic of your script. Use provided services to add more functionality to your objects: cache, parents, persistence and much more.*”

-----

|ext-icon-small| **Configuration Object** provides **powerful tools for handling configuration trees**, by converting any **configuration plain array** (which can come from sources like **TypoScript, JSON, XML**) into a much more **flexible PHP object structure**. Its principal goal is to **pull apart the configuration handling from the main logic of an application**, so the script can focus on **using the already validated configuration during its whole process**.

Problem
^^^^^^^

When a script uses a configuration tree to handle parts of an application, this tree is often **analyzed step by step during the script execution**; if a value contains a mistake, the script can be forced to stop, too early (*the whole process did not run entirely*) but also too late (*some sensitive operations may already have run*). Moreover, **the deeper** the configuration tree is, **the harder** it is to handle and prevent all the possible configuration mistakes.

When it comes to configuration which may be customized by any third-party user (which happens often in TYPO3 thanks to TypoScript), validation rules have to be **well thought and strong** to prevent the user from breaking your own API scripts because of a configuration mistake.

Solution
^^^^^^^^

Use **Configuration Object** to export the handling of your configuration: let the whole **creation and validation processes be managed outside of your application**, and enjoy the **many other features provided by the API** (cache management, parents, persistence and more).

It is **simple, fast and reliable**.

|newpage|

Example
^^^^^^^

Imagine a basic example:

.. code-block:: php

    $myCompany = [
        'name'      => 'My Company',
        'employees' => [
            [
                'name'   => 'John Doe',
                'gender' => 'Male',
                'email'  => 'john.doe@my-company.com'
            ],
            [
                'name'   => 'Jane Doe',
                'gender' => 'Female',
                'email'  => 'jane.doe@my-company.com'
            ]
        ]
    ];

When a script is going to read this array, we can imagine some simple checks which will be needed:

- ``name``: must exist and not be empty;
- ``employees``: must be an array in which each item must follow these rules:

  - ``name``: must exist and not be empty;
  - ``gender``: must exist and have one of the following values: ``Male``, ``Female``;
  - ``email``: must be a valid email address.

If we want to do all these checks, this is going to be – well, ok – quite easy, because there is **only one level** down the tree. Now, imagine we need a configuration tree which is **ten times more complex** than this one, with **much more levels down the tree**, and with **more sophisticated validation rules**. In this case, this is going to be much more annoying than the last example. In this case, you might appreciate the tools provided by this extension.

Let's see what our previous example would look like with a configuration object:

.. code-block:: php

    namespace MyVendor\MyExtensions\Model\Company;

    use Romm\ConfObj\ConfigurationObjectInterface;
    use Romm\ConfObj\Traits\ConfigurationObject\DefaultConfigurationObjectTrait;

    class Company implements ConfigurationObjectInterface
    {
        use DefaultConfigurationObjectTrait;

        /**
         * @var string
         * @validate NotEmpty
         */
        protected $name;

        /**
         * @var \ArrayObject<MyVendor\MyExtensions\Model\Company\Employee>
         */
        protected $employees;
    }

|newpage|

.. code-block:: php

    namespace MyVendor\MyExtensions\Model\Company;

    class Employee
    {

        /**
         * @var string
         * @validate NotEmpty
         */
        protected $name;

        /**
         * @var string
         * @validate NotEmpty
         * @validate Romm.ConfigurationObject:HasValues(values=Male|Female)
         */
        protected $gender;

        /**
         * @var string
         * @validate EmailAddress
         */
        protected $email;
    }

-----

.. _whyUseIt:

Why use this API?
-----------------

- **Extremely easy to set up**

  Basically, when you need a configuration object, all you need to do is write the different objects classes (in the last example, ``Company`` and ``Employee``). The API does the rest by itself.

- **Complete**

  The API provides a collection of services which can help the developer: cache, persistence, parents, and more.

- **Performance**

  When it comes to transform a plain array into a configuration object, it usually takes a few micro-seconds for the API to build it. For bigger objects, a powerful caching service is provided to prevent lack of performance.

- **Very flexible**

  It is really easy to customize the several behaviours which can affect how the configuration object is built, by using the :ref:`services <administration-services>` provided by the API.

- **Reliable**

  As the validation scope is separated from the application, you are able to know at the very beginning of the script if the configuration is **valid**, and stop the execution if errors are found. In this case, you will know precisely which properties contain mistakes, and get access to explicit error messages explaining what is wrong, and why.
