<?php
/*
 * 2017 Romain CANON <romain.hydrocanon@gmail.com>
 *
 * This file is part of the TYPO3 Configuration Object project.
 * It is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License, either
 * version 3 of the License, or any later version.
 *
 * For the full copyright and license information, see:
 * http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Romm\ConfigurationObject;

use Romm\ConfigurationObject\Core\Core;
use Romm\ConfigurationObject\Exceptions\ClassNotFoundException;
use Romm\ConfigurationObject\Exceptions\EntryNotFoundException;
use Romm\ConfigurationObject\Exceptions\WrongInheritanceException;
use Romm\ConfigurationObject\Service\DataTransferObject\GetConfigurationObjectDTO;
use Romm\ConfigurationObject\Service\Event\ConfigurationObjectAfterServiceEventInterface;
use Romm\ConfigurationObject\Service\Event\ConfigurationObjectBeforeServiceEventInterface;
use Romm\ConfigurationObject\Service\Event\ConfigurationObjectBeforeValidationServiceEventInterface;
use Romm\ConfigurationObject\Service\ServiceFactory;
use Romm\ConfigurationObject\Service\WrongServiceException;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class provides function to get a configuration object from a data array.
 *
 * Use as follow:
 *
 *  $confObject = \Romm\ConfigurationObject\ConfigurationObjectFactory::get(
 *      \MyVendor\MyExtension\Configuration\MyConfiguration::class,
 *      $myConfigurationArray
 *  );
 *
 *  if (false === $confObject->getResult()->hasErrors()) {
 *      $confObject = $confObject->getObject();
 *  } else {
 *      $errors = $confObject->getResult()->getFlattenedErrors();
 *      // Whatever you want...
 *  }
 */
class ConfigurationObjectFactory implements SingletonInterface
{

    /**
     * @var ConfigurationObjectFactory
     */
    protected static $instance;

    /**
     * @var ServiceFactory[]
     */
    protected $configurationObjectServiceFactory = [];

    /**
     * @var int
     */
    protected $runningProcesses = 0;

    /**
     * @return ConfigurationObjectFactory
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = GeneralUtility::makeInstance(self::class);
        }

        return self::$instance;
    }

    /**
     * Returns an instance of the given configuration object name. The object is
     * recursively filled with the given array data.
     *
     * @param    string $className  Name of the configuration object class. The class must implement `\Romm\ConfigurationObject\ConfigurationObjectInterface`.
     * @param    array  $objectData Data used to fill the object.
     * @return ConfigurationObjectInstance
     * @throws \Exception
     */
    public function get($className, array $objectData)
    {
        $this->runningProcesses++;

        try {
            $result = $this->convertToObject($className, $objectData);

            $this->runningProcesses--;

            return $result;
        } catch (\Exception $exception) {
            $this->runningProcesses--;

            throw $exception;
        }
    }

    /**
     * @return bool
     */
    public function isRunning()
    {
        return $this->runningProcesses > 0;
    }

    /**
     * @param string $className
     * @param array  $objectData
     * @return ConfigurationObjectInstance
     */
    protected function convertToObject($className, array $objectData)
    {
        $serviceFactory = $this->register($className);

        $dto = new GetConfigurationObjectDTO($className, $serviceFactory);
        $dto->setConfigurationObjectData($objectData);
        $serviceFactory->runServicesFromEvent(ConfigurationObjectBeforeServiceEventInterface::class, 'configurationObjectBefore', $dto);

        if (false === $dto->getResult() instanceof ConfigurationObjectInstance) {
            $mapper = $this->getConfigurationObjectMapper();
            $object = $mapper->convert($dto->getConfigurationObjectData(), $className);

            /** @var ConfigurationObjectInstance $result */
            $result = GeneralUtility::makeInstance(ConfigurationObjectInstance::class, $object, $mapper->getMessages());

            $dto->setResult($result);
        }

        $serviceFactory->runServicesFromEvent(ConfigurationObjectAfterServiceEventInterface::class, 'configurationObjectAfter', $dto);
        $serviceFactory->runServicesFromEvent(ConfigurationObjectBeforeValidationServiceEventInterface::class, 'configurationObjectBeforeValidation', $dto);

        $result = $dto->getResult();
        unset($dto);

        return $result;
    }

    /**
     * Any configuration object needs to be registered before it can be used.
     *
     * @param  string $className Class name of the wanted configuration object.
     * @return ServiceFactory
     * @throws ClassNotFoundException
     * @throws WrongInheritanceException
     * @throws WrongServiceException
     */
    protected function register($className)
    {
        if (false === isset($this->configurationObjectServiceFactory[$className])) {
            if (false === Core::get()->classExists($className)) {
                throw new ClassNotFoundException(
                    'Trying to get a non-existing configuration object: "' . $className . '".',
                    1448886437
                );
            }

            if (false === in_array(ConfigurationObjectInterface::class, class_implements($className))) {
                throw new WrongInheritanceException(
                    'The configuration object class: "' . $className . '" must implement "' . ConfigurationObjectInterface::class . '".',
                    1448886449
                );
            }

            /** @var ConfigurationObjectInterface $configurationObjectClassName */
            $configurationObjectClassName = $className;
            $serviceFactory = $configurationObjectClassName::getConfigurationObjectServices();
            if (false === $serviceFactory instanceof ServiceFactory) {
                throw new WrongServiceException(
                    'Service factory for configuration object class: "' . $className . '" must be an instance of "' . ServiceFactory::class . '".',
                    1448886479
                );
            }

            /** @var ServiceFactory $serviceFactory */
            $serviceFactory->initialize();

            $this->configurationObjectServiceFactory[$className] = $serviceFactory;
        }

        return $this->configurationObjectServiceFactory[$className];
    }

    /**
     * @return ConfigurationObjectMapper
     */
    protected function getConfigurationObjectMapper()
    {
        return Core::get()->getObjectManager()->get(ConfigurationObjectMapper::class);
    }

    /**
     * @param  string $className Name of the configuration object class.
     * @return ServiceFactory
     * @throws EntryNotFoundException
     * @internal
     */
    public function getConfigurationObjectServiceFactory($className)
    {
        if (true === isset($this->configurationObjectServiceFactory[$className])) {
            return $this->configurationObjectServiceFactory[$className];
        }

        throw new EntryNotFoundException(
            'Trying to access to an instance of "ConfigurationObjectServiceFactory" which is not registered for the class "' . $className . '".',
            1471278490
        );
    }
}
