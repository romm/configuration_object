.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt

.. _installation:

Installation
============

**Composer**

If you are working with Composer (which you should), require ``romm/configuration-object``. You should now have in your ``composer.json`` something
like:

.. code-block:: javascript

    "require": {
        ...
        "romm/configuration-object": "*"
        ...
    }

**TER**

You can download the extension on the TER: https://typo3.org/extensions/repository/view/configuration_object

**Dependency**

Do not forget to update the ``ext_emconf.php`` of your extension, and add the ``configuration_object`` dependency:

.. code-block:: php

    $EM_CONF[$_EXTKEY] = [
        ...
        'constraints' => [
            'depends'   => [
                'configuration_object' => '1.0.0-1.99.99'
            ]
        ]
        ...
    ];