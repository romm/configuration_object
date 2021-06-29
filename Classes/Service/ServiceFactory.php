<?php
/*
 * 2018 Romain CANON <romain.hydrocanon@gmail.com>
 *
 * This file is part of the TYPO3 Configuration Object project.
 * It is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License, either
 * version 3 of the License, or any later version.
 *
 * For the full copyright and license information, see:
 * http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Romm\ConfigurationObject\Service;

use Romm\ConfigurationObject\Core\Core;
use Romm\ConfigurationObject\Core\Service\ReflectionService;
use Romm\ConfigurationObject\Exceptions\DuplicateEntryException;
use Romm\ConfigurationObject\Exceptions\EntryNotFoundException;
use Romm\ConfigurationObject\Exceptions\Exception;
use Romm\ConfigurationObject\Exceptions\InitializationNotSetException;
use Romm\ConfigurationObject\Exceptions\InvalidTypeException;
use Romm\ConfigurationObject\Exceptions\MethodNotFoundException;
use Romm\ConfigurationObject\Exceptions\WrongInheritanceException;
use Romm\ConfigurationObject\Service\DataTransferObject\AbstractServiceDTO;
use Romm\ConfigurationObject\Service\Event\ServiceEventInterface;
use Romm\ConfigurationObject\Traits\InternalVariablesTrait;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class will handle the several services which will be used for a
 * configuration object.
 *
 * You may customize which services are used by overriding the function
 * `getConfigurationObjectServices` in your configuration object root class.
 *
 * Example:
 *
 *  public static function getConfigurationObjectServices()
 *  {
 *      return ServiceFactory::getInstance()
 *          ->attach(CacheService::class)
 *          ->setOption(CacheService::OPTION_CACHE_NAME, 'my_custom_cache')
 *          ->attach(MyCustomService::class, ['foo' => 'bar'])
 *          ->setOption('optionForMyCustomService', 'foo');
 *  }
 */
class ServiceFactory
{
    use InternalVariablesTrait;

    /**
     * @var array
     */
    protected $service = [];

    /**
     * @var AbstractService[]
     */
    protected $serviceInstances = [];

    /**
     * @var bool
     */
    protected $hasBeenInitialized = false;

    /**
     * @var array
     */
    protected static $instances = [];

    /**
     * @var array
     */
    protected $servicesEvents = [];

    /**
     * @var string
     */
    private $currentService;

    /**
     * @var array
     */
    protected static $servicesChecked = [];

    /**
     * Main function to get an empty instance of ServiceFactory.
     *
     * @return ServiceFactory
     */
    public static function getInstance()
    {
        return Core::get()->getServiceFactoryInstance();
    }

    /**
     * Will attach a service to this factory.
     *
     * It will also reset the current service value to the given service,
     * allowing the usage of the function `setOption` with this service.
     *
     * @param string $serviceClassName The class name of the service which will be attached.
     * @param array  $options          Array of options which will be sent to the service.
     * @return $this
     * @throws DuplicateEntryException
     */
    public function attach($serviceClassName, array $options = [])
    {
        if (true === isset($this->service[$serviceClassName])) {
            throw new DuplicateEntryException(
                'The service "' . $serviceClassName . '" was already attached to the service factory.',
                1456418859
            );
        }

        $this->currentService = $serviceClassName;
        $this->service[$serviceClassName] = [
            'className' => $serviceClassName,
            'options'   => $options
        ];

        return $this;
    }

    /**
     * Checks if this factory contains the given service.
     *
     * @param string $serviceClassName The class name of the service which should be attached.
     * @return bool
     */
    public function has($serviceClassName)
    {
        return true === isset($this->service[$serviceClassName]);
    }

    /**
     * Returns the wanted service, if it was previously registered.
     *
     * @param string $serviceClassName The class name of the wanted service.
     * @return AbstractService
     * @throws InitializationNotSetException
     * @throws EntryNotFoundException
     */
    public function get($serviceClassName)
    {
        if (false === $this->hasBeenInitialized) {
            throw new InitializationNotSetException(
                'You can get a service instance only when the service factory has been initialized.',
                1456419587
            );
        }

        if (false === $this->has($serviceClassName)) {
            throw new EntryNotFoundException(
                'The service "' . $serviceClassName . '" was not found in this service factory. Attach it before trying to get it!',
                1456419653
            );
        }

        return $this->serviceInstances[$serviceClassName];
    }

    /**
     * Resets the current service value to the given service, allowing the usage
     * of the function `setOption` with this service.
     *
     * @param string $serviceClassName The class name of the service which will be saved as "current service".
     * @return $this
     * @throws Exception
     */
    public function with($serviceClassName)
    {
        if (false === $this->has($serviceClassName)) {
            throw new Exception(
                'You cannot use the function "' . __FUNCTION__ . '" on a service which was not added to the factory (service used: "' . $serviceClassName . '").',
                1459425398
            );
        }

        $this->currentService = $serviceClassName;

        return $this;
    }

    /**
     * Allows the modification of a given option for the current service (which
     * was defined in the function `with()` or the function `attach()`).
     *
     * @param string $optionName  Name of the option to modify.
     * @param string $optionValue New value of the option.
     * @return $this
     * @throws InitializationNotSetException
     */
    public function setOption($optionName, $optionValue)
    {
        if (null === $this->currentService) {
            throw new InitializationNotSetException(
                'To set the option "' . (string)$optionName . '" you need to indicate a service first, by using the function "with".',
                1456419282
            );
        }

        $this->service[$this->currentService]['options'][$optionName] = $optionValue;

        return $this;
    }

    /**
     * Returns an option for the current service. If the option is not found,
     * `null` is returned.
     *
     * @param string $optionName
     * @return mixed
     */
    public function getOption($optionName)
    {
        return (isset($this->service[$this->currentService]['options'][$optionName]))
            ? $this->service[$this->currentService]['options'][$optionName]
            : null;
    }

    /**
     * Initializes every single service which was added in this instance.
     *
     * @throws WrongInheritanceException
     * @internal This function is reserved for internal usage only, you should not use it in third party applications!
     */
    public function initialize()
    {
        if (true === $this->hasBeenInitialized) {
            return;
        }

        $this->hasBeenInitialized = true;

        foreach ($this->service as $service) {
            list($serviceClassName, $serviceOptions) = $this->manageServiceData($service);

            $this->serviceInstances[$serviceClassName] = $this->getServiceInstance($serviceClassName, $serviceOptions);
        }
    }

    /**
     * @param string $className
     * @param array  $options
     * @return AbstractService
     * @throws WrongInheritanceException
     */
    protected function getServiceInstance($className, array $options)
    {
        $flag = false;
        $serviceInstance = null;

        if (Core::get()->classExists($className)) {
            /** @var AbstractService $serviceInstance */
            $serviceInstance = Core::get()->getObjectManager()->get($className);

            if ($serviceInstance instanceof AbstractService) {
                $serviceInstance->initializeObject($options);
                $serviceInstance->initialize();
                $flag = true;
            }
        }

        if (false === $flag) {
            throw new WrongInheritanceException(
                'Trying to initialize ConfigurationObject with a wrong service: "' . $className . '".',
                1448886469
            );
        }

        return $serviceInstance;
    }

    /**
     * Will loop on each registered service in this factory, and check if they
     * use the requested event. If they do, the event is dispatched.
     *
     * @param string             $serviceEvent    The class name of the service event.
     * @param string             $eventMethodName Name of the method called in the service.
     * @param AbstractServiceDTO $dto             The data transfer object sent to the services.
     * @throws Exception
     * @throws InvalidTypeException
     * @throws WrongInheritanceException
     * @internal This function is reserved for internal usage only, you should not use it in third party applications!
     */
    public function runServicesFromEvent($serviceEvent, $eventMethodName, AbstractServiceDTO $dto)
    {
        if (false === $this->hasBeenInitialized) {
            return;
        }

        $this->checkServiceEvent($serviceEvent);
        $this->checkServiceEventMethodName($serviceEvent, $eventMethodName);

        $serviceInstances = $this->getServicesFromEvent($serviceEvent);

        if (count($serviceInstances) > 0) {
            foreach ($serviceInstances as $serviceInstance) {
                $serviceInstance->$eventMethodName($dto);
            }

            $serviceInstances[0]->runDelayedCallbacks($dto);
        }
    }

    /**
     * Will check if the class of the service event is correct and implements
     * the correct interface.
     *
     * @param string $serviceEvent The class name of the service event.
     * @throws WrongInheritanceException
     */
    protected function checkServiceEvent($serviceEvent)
    {
        if (false === isset(self::$servicesChecked[$serviceEvent])) {
            self::$servicesChecked[$serviceEvent] = [];

            if (false === in_array(ServiceEventInterface::class, class_implements($serviceEvent))) {
                throw new WrongInheritanceException(
                    'Trying to run services with a wrong event: "' . $serviceEvent . '". Service events must extend "' . ServiceEventInterface::class . '".',
                    1456409155
                );
            }
        }
    }

    /**
     * Will check if the given method exists in the service event.
     *
     * @param string $serviceEvent    The class name of the service event.
     * @param string $eventMethodName Name of the method called in the service.
     * @throws MethodNotFoundException
     */
    protected function checkServiceEventMethodName($serviceEvent, $eventMethodName)
    {
        if (false === in_array($eventMethodName, self::$servicesChecked[$serviceEvent])) {
            $eventClassReflection = ReflectionService::get()->getClassReflection($serviceEvent);
            self::$servicesChecked[$serviceEvent][] = $eventMethodName;

            if (false === $eventClassReflection->hasMethod($eventMethodName)) {
                throw new MethodNotFoundException(
                    'The service event "' . $serviceEvent . '" does not have a method called "' . $eventMethodName . '".',
                    1456509926
                );
            }
        }
    }

    /**
     * Will loop trough all the services instance and get the ones which use the
     * given event.
     *
     * @param string $serviceEvent The class name of the service event.
     * @return AbstractService[]
     */
    protected function getServicesFromEvent($serviceEvent)
    {
        if (false === isset($this->servicesEvents[$serviceEvent])) {
            $servicesInstances = [];
            foreach ($this->serviceInstances as $serviceInstance) {
                if ($serviceInstance instanceof $serviceEvent) {
                    $servicesInstances[] = $serviceInstance;
                }
            }

            $this->servicesEvents[$serviceEvent] = $servicesInstances;
        }

        return $this->servicesEvents[$serviceEvent];
    }

    /**
     * This function is here to allow unit tests to override data.
     *
     * @param array $service
     * @return array
     * @internal
     */
    protected function manageServiceData(array $service)
    {
        return [$service['className'], $service['options']];
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return spl_object_hash($this);
    }
}
