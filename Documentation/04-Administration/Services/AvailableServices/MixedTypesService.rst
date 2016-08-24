.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt

|newpage|

.. _administration-service-mixedTypesService:

Mixed types service
===================

This service allows **properties types to be dynamically fetched**.

For instance, in a configuration object which contains a list of employees, **every employee can have a different role**; in this case, roles can be **divided into PHP classes**. This service will allow the implementation of a **method which will be used to resolve which class should be used**. Take a look at the example below for more information.

Usage
-----

You can activate this service for a given configuration object by attaching it to the ``ServiceFactory`` in the static function ``getConfigurationObjectServices()``. Use the constant :php:`ServiceInterface::SERVICE_MIXED_TYPES` as an identifier for this service (see example below).

You then have to implement the interface :php:`MixedTypesInterface` in every class which needs this feature, and write your own :php:`public static function getInstanceClassName(MixedTypesResolver $resolver)` implementation.

Example
-------

.. code-block:: php
    :linenos:
    :emphasize-lines: 34,41,46-47,52,77-95,132,139,143

    use Romm\ConfObj\ConfigurationObjectInterface;
    use Romm\ConfObj\Service\ServiceInterface;
    use Romm\ConfObj\Traits\ConfigurationObject\DefaultConfigurationObjectTrait;
    use Romm\ConfObj\Traits\ConfigurationObject\MagicMethodsTrait;
    use Romm\ConfObjTest\Model\Company\Employee\AbstractEmployee;
    use Romm\ConfObj\Service\Items\MixedTypes\MixedTypesInterface;
    use Romm\ConfObj\Service\Items\MixedTypes\MixedTypesResolver;

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

    $myCompany = ConfigurationObjectFactory::get(
        Company::class,
        $companyConfigurationArray
    );