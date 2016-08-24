.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt


.. _administration-validators:

Validators
==========

Extbase brings a way to easily tell TYPO3 when a property is valid: by using **annotations** on class properties, Extbase will find which properties should be validated. To do so, you have to use the ``@validate`` annotation inside the phpDoc section of that property.

Of course, you can create and use any validator you want in your configuration object – if it does follow Extbase validator standards.

**Example:**

.. code-block:: php

    /**
     * @var string
     * @validate TYPO3\CMS\Extbase\Validation\Validator\NotEmptyValidator
     */
    protected $name;

.. tip::

    To reduce the length of a ``@validate`` annotation, you can use the following shortcut naming convention: ``VendorName.ExtensionName:ValidatorName``.

    Moreover, a validator from the core of Extbase can be used by writing only its name.

    **Example**

    .. code-block:: php

        /**
         * @var string
         * @validate NotEmpty
         * @validate EmailAddress
         * @validate MyVendor.MyExtension:Custom
         */
        protected $email;

    Please be aware that only validators which follow the Extbase validator naming convention can use the shortened notation:

    1. The validator must be in the following namespace of your extension: ``Vendor\ExtensionName\Validation\Validator``;
    2. The validator class must end with ``Validator``.

    Oh, by the way, you can use this notation everywhere Extbase parses the ``@validate`` annotation, for instance in your controller or model classes. :-)

**Parameters**

Some validators may require parameters, for instance ``NumberRangeValidator`` from Extbase needs the following parameters: ``minimum`` and ``maximum``. To do so, use the following notation:

.. code-block:: php

    /**
     * @validate NumberRange(minimum: 0, maximum: 100)
     */

**Multiple validators**

You can attach several validators to the same property. For instance:

.. code-block:: php

    /**
     * @var string
     * @validate NotEmpty
     * @validate EmailAddress
     * @validate VendorName.ExtensionName:CustomValidator
     */
    protected $email;