.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _administration:

Administration
==============


.. only:: html

    Using this API, you will mainly work with three tools:

    - **Services**

      They can be attached on any configuration object you want, and add more functionality to your objects: cache handling, persistence handling, parents routing, etc.

      Check the chapter “:ref:`administration-services`” for further information.

    - **Utilities**

      You have access to some utilities which do not require to activate a service in order to work.

      Find them in the chapter “:ref:`administration-utilities`”.

    - **Validators**

      They will help you handling the rules you want for your configuration objects. You can attach any validator following Extbase convention on each property of your objects.

      Check the chapter “:ref:`administration-validators`” for further information.

.. toctree::
    :titlesonly:
    :hidden:

    Services/Index
    Utilities/Index
    Validators/Index
    UnitTests/Index
