.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt

.. _administration-service-parentsService:

Parents service
===============

This service will allow an object inside the full configuration object tree to fetch its parent object(s). For example, an object ``A`` has a property containing several instances of ``B``, if the parent service is activated for the class ``B``, then every instance of this class can access to its parents (in this example ``A``).

A good usage instance with this service is managing very easily default configuration. Imagine an object which has an optional property: if this property is not filled in the configuration array, then a default one must be used. You can then fetch the default object instance by going back to the parent which contains it. See the function ``getFoo()`` in the example below.

Usage
-----

You can activate this service for a given configuration object by attaching it to the ``ServiceFactory`` in the static function ``getConfigurationObjectServices()``. Use the constant :php:`ServiceInterface::SERVICE_PARENTS` as an identifier for this service (see example below).

You then have to import the trait :php:`ParentsTrait` in every class which needs to have access to its parents. It gives you access to the following functions:

=============================================================== =============================================================================================
Name                                                            Description
=============================================================== =============================================================================================
``hasParent($class)``                                           Will return true if the object does have a parent of the given class.

``getFirstParent($class)``                                      Returns the first parent matching the given class name. Returns ``null`` if no parent with
                                                                that class was found.

``withFirstParent($class, $callback, $notFoundCallback)``       Allows executing a callback function with the first parent found which is of the wanted type.
                                                                If not parent with this class is found, ``$notFoundCallback`` is called.
=============================================================== =============================================================================================

Example
-------

.. code-block:: php
    :linenos:
    :emphasize-lines: 27,43,61,82

    use Romm\ConfigurationObject\Service\ServiceInterface;
    use Romm\ConfigurationObject\Service\ServiceFactory;
    use Romm\ConfigurationObject\ConfigurationObjectInterface;
    use Romm\ConfigurationObject\Traits\ConfigurationObject\DefaultConfigurationObjectTrait;
    use Romm\ConfigurationObject\Service\Items\Parents\ParentsTrait;

    class MyObject implements ConfigurationObjectInterface
    {
        use DefaultConfigurationObjectTrait;

        /**
         * @var SubObject[]
         */
        protected $subObjects;

        /**
         * @var SubObject
         */
        protected $defaultSubObject;

        /**
         * @return ServiceFactory
         */
        public static function getConfigurationObjectServices()
        {
            return ServiceFactory::getInstance()
                ->attach(ServiceInterface::SERVICE_PARENTS);
        }

        /**
         * @return SubObject
         */
        public function getDefaultSubObject()
        {
            return $this->defaultSubObject();
        }
    }

    class MySubObject
    {
        const DEFAULT_FOO = 'foo';

        use ParentsTrait;

        /**
         * @var string
         */
        protected $foo;

        /**
         * @var string
         */
        protected $bar;

        /**
         * @return string
         */
        public function getFoo()
        {
            if (null === $this->foo) {
                $this->foo = $this->withFirstParent(
                    MyObject::class,
                    function(MyObject $myObject) {
                        return $myObject->getDefaultSubObject()->getFoo();
                    },
                    function() {
                        return (null === $this->foo)
                            ? MySubObject::DEFAULT_FOO
                            : $this->foo;
                    }
                )
            }

            return $this->foo;
        }

        /**
         * @return string
         */
        public function getBar()
        {
            if ($this->hasParent(MyObject::class)
                && null === $this->bar
            ) {
                $this->bar = SomeRandomClass::runSomeExternalService();
            }

            return $this->bar;
        }
    }
