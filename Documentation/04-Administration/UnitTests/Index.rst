.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt


.. _administration-unitTests:

Unit tests
==========

When creating unit tests for your extension, you might need to create configuration objects. You will need to initialize Configuration Object services to make the extension work with tests.

First, use the trait ``ConfigurationObjectUnitTestUtility`` in your test class, then call the function ``initializeConfigurationObjectTestServices()`` in the function ``setUp()``.

**Example:**

.. code-block:: php
    :linenos:
    :emphasize-lines: 7,11

    use TYPO3\CMS\Core\Tests\UnitTestCase;
    use Romm\ConfObj\Tests\Unit\ConfigurationObjectUnitTestUtility;

    class MyTest extends UnitTestCase
    {

        use ConfigurationObjectUnitTestUtility;

        protected function setUp()
        {
            $this->initializeConfigurationObjectTestServices();
        }

        /**
         * @test
         */
        public function myTest()
        {
            $configurationArray = ['...'];

            ConfigurationObjectFactory::getInstance()
                    ->get(MyObject::class, $configurationArray);
        }
    }
