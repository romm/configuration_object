<?php
/*
 * 2016 Romain CANON <romain.hydrocanon@gmail.com>
 *
 * This file is part of the TYPO3 Configuration Object project.
 * It is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License, either
 * version 3 of the License, or any later version.
 *
 * For the full copyright and license information, see:
 * http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Romm\ConfigurationObject\Service\DataTransferObject;

use Romm\ConfigurationObject\ConfigurationObjectInterface;
use Romm\ConfigurationObject\Core\Core;
use Romm\ConfigurationObject\Exceptions\ClassNotFoundException;
use Romm\ConfigurationObject\Exceptions\WrongInheritanceException;
use Romm\ConfigurationObject\Service\ServiceFactory;

/**
 * Abstract class for a service data transfer object.
 */
abstract class AbstractServiceDTO
{

    /**
     * @var string
     */
    protected $configurationObjectClassName;

    /**
     * @var ServiceFactory
     */
    protected $serviceFactory;

    /**
     * Constructor.
     *
     * @param string         $className      Name of the class of the configuration object being processed.
     * @param ServiceFactory $serviceFactory Instance of the `ServiceFactory` used by the current configuration object being built.
     */
    public function __construct($className, ServiceFactory $serviceFactory)
    {
        $this->setConfigurationObjectClassName($className);
        $this->setServiceFactory($serviceFactory);
    }

    /**
     * @return string
     */
    public function getConfigurationObjectClassName()
    {
        return $this->configurationObjectClassName;
    }

    /**
     * @param   string $className
     * @return $this
     * @throws ClassNotFoundException
     * @throws WrongInheritanceException
     */
    protected function setConfigurationObjectClassName($className)
    {
        if (false === Core::classExists($className)) {
            throw new ClassNotFoundException('The class "' . $className . '" does not exist.', 1456002532);
        }
        if (false === is_subclass_of($className, ConfigurationObjectInterface::class)) {
            throw new WrongInheritanceException('The class "' . $className . '" must implement "' . ConfigurationObjectInterface::class . '".', 1456002645);
        }

        $this->configurationObjectClassName = $className;

        return $this;
    }

    /**
     * @return ServiceFactory
     */
    public function getServiceFactory()
    {
        return $this->serviceFactory;
    }

    /**
     * @param ServiceFactory $serviceFactory
     */
    protected function setServiceFactory(ServiceFactory $serviceFactory)
    {
        $this->serviceFactory = $serviceFactory;
    }
}
