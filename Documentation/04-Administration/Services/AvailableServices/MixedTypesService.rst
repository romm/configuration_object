.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt

.. _administration-service-mixedTypesService:

Mixed types service
===================

This service allows **properties types to be dynamically fetched**.

For instance, in a configuration object which contains a list of employees, **every employee can have a different role**; in this case, roles can be **divided into PHP classes**. This service will allow the implementation of a **method that will be called to resolve which class should be used**. Take a look at the example below for more information.

Usage
-----

You can activate this service for a given configuration object by attaching it to the ``ServiceFactory`` in the static function ``getConfigurationObjectServices()``. Use the constant :php:`ServiceInterface::SERVICE_MIXED_TYPES` as an identifier for this service (see example below).

You have two possibilities to use it on properties:

- **Solution 1**: you can use the annotation ``@mixedTypesResolver`` on the property that needs it, filled with a class name that implements the interface :php:`MixedTypesInterface`.

- **Solution 2**: the type of the variable can be the resolver class, for instance an abstract class.

The resolver class has to implement the interface :php:`MixedTypesInterface`, and have its own :php:`public static function getInstanceClassName(MixedTypesResolver $resolver)` implementation.

Example
-------

**Solution 1:**

.. code-block:: php
    :linenos:
    :emphasize-lines: 7,25

    class Configuration implements ConfigurationObjectInterface
    {
        use DefaultConfigurationObjectTrait;

        /**
         * @var FooInterface[]
         * @mixedTypesResolver FooResolver
         */
        protected $foo;
    }

    class FooResolver implements MixedTypesInterface
    {
        /**
         * @inheritdoc
         */
        public static function getInstanceClassName(MixedTypesResolver $resolver)
        {
            $data = $resolver->getData();

            $type = ($data['type'] === 'foo')
                ? Foo::class
                : Bar::class;

            $resolver->setObjectType($type);
        }
    }

    class Foo implements FooInterface
    {
        // Some code...
    }

    class Bar implements FooInterface
    {
        // Some other code...
    }

**Solution 2:**

.. code-block:: php
    :linenos:
    :emphasize-lines: 34,41,46-47,52,77-95,132,139,143

    use Romm\ConfigurationObject\ConfigurationObjectInterface;
    use Romm\ConfigurationObject\Service\ServiceInterface;
    use Romm\ConfigurationObject\Traits\ConfigurationObject\DefaultConfigurationObjectTrait;
    use Romm\ConfigurationObject\Traits\ConfigurationObject\MagicMethodsTrait;
    use Romm\ConfObjTest\Model\Company\Employee\AbstractEmployee;
    use Romm\ConfigurationObject\Service\Items\MixedTypes\MixedTypesInterface;
    use Romm\ConfigurationObject\Service\Items\MixedTypes\MixedTypesResolver;

    /**
     * COMPANY
     */
    class Company implements ConfigurationObjectInterface
    {

        use DefaultConfigurationObjectTrait;
        use MagicMethodsTrait;

        /**
         * @var string
         */
        protected $name;

        /**
         * @var \ArrayObject<AbstractEmployee>
         */
        protected $employees;

        /**
         * @return ServiceFactory
         */
        public static function getConfigurationObjectServices()
        {
            return DefaultConfigurationObjectTrait::getConfigurationObjectServices()
                ->attach(ServiceInterface::SERVICE_MIXED_TYPES);
        }
    }

    /**
     * ABSTRACT EMPLOYEE
     */
    abstract class AbstractEmployee implements MixedTypesInterface
    {

        use MagicMethodsTrait;

        const TYPE_MECHANIC = Mechanic::class;
        const TYPE_SECRETARY = Secretary::class;

        /**
         * @var array
         */
        protected static $allowedTypes = [self::TYPE_MECHANIC, self::TYPE_SECRETARY];

        /**
         * @var string
         */
        protected $name;

        /**
         * The employee doing his job.
         */
        abstract public function work();

        /**
         * The employee talking.
         *
         * @param string $message
         */
        public function talk($message)
        {
            echo $message;
        }

        /**
         * @inheritdoc
         */
        public static function getInstanceClassName(MixedTypesResolver $resolver)
        {
            $data = $resolver->getData();

            if (in_array($data['type'], self::$allowedTypes)) {
                $type = $data['type'];

                $resolver->setObjectType($type);
            } else {
                $error = new Error(
                    'Type for this property is not correct, it should be one of the ' .
                    'following values: "'. implode('", "', self::$allowedTypes) .
                    '". Current value: "' . $data['type'] . '".',
                    1471871884
                );

                $resolver->addError($error);
            }
        }
    }

    /**
     * MECHANIC EMPLOYEE
     */
    class Mechanic extends AbstractEmployee
    {

        /**
         * @inheritdoc
         */
        public function work()
        {
            $sentence = 'Hello my name is "' . $this->getName() . '" and I am ' .
            'currently repairing a car.';

            $this->talk($sentence);
        }
    }

    /**
     * SECRETARY EMPLOYEE
     */
    class Secretary extends AbstractEmployee
    {

        /**
         * @inheritdoc
         */
        public function work()
        {
            $sentence = 'Hello my name is "' . $this->getName() . '" and I am ' .
            'currently organizing meetings.';

            $this->talk($sentence);
        }
    }

    $companyConfigurationArray = [
        'name'      => 'My Company',
        'employees' => [
            [
                'name' => 'John Doe',
                'type' => AbstractEmployee::TYPE_MECHANIC
            ],
            [
                'name' => 'Jane Doe',
                'type' => AbstractEmployee::TYPE_SECRETARY
            ]
        ]
    ];

    $myCompany = ConfigurationObjectFactory::convert(
        Company::class,
        $companyConfigurationArray
    );
