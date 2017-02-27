.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt

.. _administration-utilities:

Utilities
=========

In addition to services, you can use other utilities provided by the API. They do not require a service to work, and can be used very easily in any class of a configuration (sub)object.

- :ref:`administration-utilities-arrayConversion`

  Provides the function ``toArray()`` to convert any configuration (sub)object into a plain array.

- :ref:`administration-utilities-storeArrayIndex`

  Will store the index of the array entries during a configuration object conversion.

- :ref:`administration-utilities-magicMethods`

  Provides automatic handling of properties getters and setters.

-----

.. _administration-utilities-arrayConversion:

Array conversion
----------------

The trait :php:`ArrayConversionTrait` provides the function ``toArray()`` which will recursively convert the object and its potential sub-objects into a plain array.

**Example:**

.. code-block:: php
    :linenos:
    :emphasize-lines: 9,21

    use Romm\ConfObj\ConfigurationObjectInterface;
    use Romm\ConfObj\ConfigurationObjectFactory;
    use Romm\ConfObj\Traits\ConfigurationObject\DefaultConfigurationObjectTrait;
    use Romm\ConfObj\Traits\ConfigurationObject\ArrayConversionTrait;

    class MyObject implements ConfigurationObjectInterface
    {
        use DefaultConfigurationObjectTrait;
        use ArrayConversionTrait;

        /**
         * @var \ArrayObject<SubObject>
         */
        protected $subObjects;
    }

    $myConfigurationObject = ConfigurationObjectFactory::get(
        MyObject::class,
        $someConfigurationArray
    );
    $myConfigurationArray = $myConfigurationObject->getObject()->toArray();

-----

.. _administration-utilities-storeArrayIndex:

Store array index
-----------------

In some cases, a sub-object of a configuration object can be stored in an array, at a given index. In this case, you may want to access to this index directly from within the sub-object. If you need to, just use the trait :php:`StoreArrayIndexTrait` in the class which needs it. You then have access to the function ``getArrayIndex()`` which returns the index in which the object was stored.

**Example:**

.. code-block:: php
    :linenos:
    :emphasize-lines: 26,47

    use Romm\ConfObj\ConfigurationObjectInterface;
    use Romm\ConfObj\ConfigurationObjectFactory;
    use Romm\ConfObj\Traits\ConfigurationObject\DefaultConfigurationObjectTrait;
    use Romm\ConfObj\Traits\ConfigurationObject\StoreArrayIndexTrait;

    class MyObject implements ConfigurationObjectInterface
    {
        use DefaultConfigurationObjectTrait;

        /**
         * @var \ArrayObject<SubObject>
         */
        protected $subObjects;

        /**
         * @return SubObject[]
         */
        public function getSubObjects()
        {
            return $this->subObjects;
        }
    }

    class MySubObject
    {
        use StoreArrayIndexTrait;

        /**
         * @var string
         */
        protected $foo;
    }

    $someConfigurationArray = [
        'someIndex'      => ['foo' => 'bar'],
        'someOtherIndex' => ['foo' => 'bar']
    ]

    $myConfigurationObject = ConfigurationObjectFactory::get(
        MyObject::class,
        $someConfigurationArray
    );
    $myObject = $myConfigurationObject->getObject();

    foreach ($myObject->getSubObjects() as $subObject) {
        // Should display `someIndex`, then `someOtherIndex`.
        var_dump($subObject->getArrayIndex());
    }

-----

.. _administration-utilities-magicMethods:

Magic setters/getters
---------------------

Setting up getter/setter functions in an object is often the **same boring basic logic**, consisting in two one-line functions which do nothing more than just setting/returning a property of the object.

Because objects can have a lot of properties, you may want not to be forced to write down these functions for every property. You can then use the trait :php:`MagicMethodsTrait` which will handle magic calls to the setters/getters of the object's properties.

**Example:**

.. code-block:: php
    :linenos:
    :emphasize-lines: 8,28-30

    use Romm\ConfObj\ConfigurationObjectInterface;
    use Romm\ConfObj\Traits\ConfigurationObject\DefaultConfigurationObjectTrait;
    use Romm\ConfObj\Traits\ConfigurationObject\MagicMethodsTrait;

    class MyObject implements ConfigurationObjectInterface
    {
        use DefaultConfigurationObjectTrait;
        use MagicMethodsTrait;

        /**
         * @var string
         */
        protected $foo;

        /**
         * @var string
         */
        protected $bar;

        // No setter/getter in here...
    }

    $myConfigurationObject = ConfigurationObjectFactory::get(
        MyObject::class,
        $someConfigurationArray
    );
    $myObject = $myConfigurationObject->getObject();
    $foo = $myObject->getFoo(); // Will work.
    $bar = $myObject->getBar(); // Will work as well.
    $bar = $myObject->setBar('bar'); // You got it? :)

.. note::

    If for some reason you want to disable the magic methods for a given property, you need to tag it with ``@disableMagicMethods``.

    **Example:**

    .. code-block:: php
        :linenos:
        :emphasize-lines: 19

        use Romm\ConfObj\ConfigurationObjectInterface;
        use Romm\ConfObj\Traits\ConfigurationObject\DefaultConfigurationObjectTrait;
        use Romm\ConfObj\Traits\ConfigurationObject\MagicMethodsTrait;

        class MyObject implements ConfigurationObjectInterface
        {
            use DefaultConfigurationObjectTrait;
            use MagicMethodsTrait;

            /**
             * @var string
             */
            protected $foo;

            /**
             * This property will not be accessible by magic setter/getter.
             *
             * @var string
             * @disableMagicMethods
             */
            protected $bar;
        }

.. hint::

    The usage of this trait will not provide auto-completion in IDEs like PhpStorm. But you can still use the class annotation ``@method`` (see `phpDoc official documentation <https://phpdoc.org/docs/latest/references/phpdoc/tags/method.html>`_) to simulate it.

    For instance, the example above would look like:

    .. code-block:: php
        :linenos:
        :emphasize-lines: 2-5

        /**
         * @method setFoo(string $foo)
         * @method string getFoo()
         * @method setBar(string $bar)
         * @method string getBar()
         */
        class MyObject implements ConfigurationObjectInterface
        {
            // ...
        }
