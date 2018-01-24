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

namespace Romm\ConfigurationObject\Service\Items\Persistence;

use Romm\ConfigurationObject\Core\Core;
use Romm\ConfigurationObject\Service\AbstractService;
use Romm\ConfigurationObject\Service\DataTransferObject\ConfigurationObjectConversionDTO;
use Romm\ConfigurationObject\Service\DataTransferObject\GetConfigurationObjectDTO;
use Romm\ConfigurationObject\Service\Event\ConfigurationObjectAfterServiceEventInterface;
use Romm\ConfigurationObject\Service\Event\ObjectConversionBeforeServiceEventInterface;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Extbase\Utility\ArrayUtility;

/**
 * If activated, this service will handle the TYPO3 domain object properties of
 * a configuration object (e.g. a frontend user: "fe_users").
 *
 * When the configuration object is mapped, every property will be checked, and
 * if its type is a domain object, it will be handled, meaning that at the end
 * of the mapping, the domain objects will be fetched from Extbase persistence.
 *
 * For example, you can have a property like this one:
 *  /**
 *   * @var \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
 *   * /
 *  protected $user;
 *
 * This means that in your configuration array, the value of the property will
 * be the uid of the frontend user you actually want.
 *
 * Using this persistence service will allow you to combine both the power of
 * the caching framework and the Extbase persistence, which will fetch the last
 * updated instance of the domain objects.
 */
class PersistenceService extends AbstractService implements ObjectConversionBeforeServiceEventInterface, ConfigurationObjectAfterServiceEventInterface
{

    /**
     * This event needs to be called before the object is stored in cache, as it
     * will add internal variables to the object which are used further to fetch
     * the domain objects.
     */
    const PRIORITY_SAVE_DOMAIN_OBJECTS_PATHS = -500;

    /**
     * This event needs to be called after the object is obtained from cache.
     */
    const PRIORITY_FETCH_DOMAIN_OBJECTS = -5000;

    /**
     * Paths to properties which are domain objects.
     *
     * @var array
     */
    protected $domainObjectsPaths = [];

    /**
     * @var PersistenceManager
     */
    protected $persistenceManager;

    /**
     * Will perform a check on every property of the configuration object: if
     * the property is a `DomainObjectInterface`, the path to the property will
     * be added to a local storage for further usage.
     *
     * @param ConfigurationObjectConversionDTO $serviceDataTransferObject
     */
    public function objectConversionBefore(ConfigurationObjectConversionDTO $serviceDataTransferObject)
    {
        if (Core::get()->classExists($serviceDataTransferObject->getTargetType())
            && in_array(DomainObjectInterface::class, class_implements($serviceDataTransferObject->getTargetType()))
        ) {
            $serviceDataTransferObject->setResult($serviceDataTransferObject->getSource());

            $path = implode('.', $serviceDataTransferObject->getCurrentPropertyPath());
            $this->domainObjectsPaths[$path] = $serviceDataTransferObject->getTargetType();
        }
    }

    /**
     * This function will first save the entire storage of properties paths set
     * in the function `objectConversionBefore`.
     *
     * Then, each of the properties above will be fetched from Extbase
     * persistence.
     *
     * @param GetConfigurationObjectDTO $serviceDataTransferObject
     */
    public function configurationObjectAfter(GetConfigurationObjectDTO $serviceDataTransferObject)
    {
        // Will save the registered domain object properties paths.
        $this->delay(
            self::PRIORITY_SAVE_DOMAIN_OBJECTS_PATHS,
            function (GetConfigurationObjectDTO $serviceDataTransferObject) {
                if (false === empty($this->domainObjectsPaths)) {
                    $serviceDataTransferObject->getResult()
                        ->setInternalVar('domainObjectsPaths', $this->domainObjectsPaths);
                }
            }
        );

        // Will fetch real object instances of the registered domain object properties.
        $this->delay(
            self::PRIORITY_FETCH_DOMAIN_OBJECTS,
            function (GetConfigurationObjectDTO $serviceDataTransferObject) {
                $domainObjectPaths = $serviceDataTransferObject->getResult()->getInternalVar('domainObjectsPaths');

                if (false === empty($domainObjectPaths)) {
                    foreach ($domainObjectPaths as $path => $type) {
                        $this->fetchDomainObjectsInternal(
                            $serviceDataTransferObject->getResult()->getObject(true),
                            explode('.', $path),
                            $type
                        );
                    }
                }
            }
        );
    }

    /**
     * Internal function for `configurationObjectAfter()`
     *
     * @param   mixed  $value                   Current value (object/array).
     * @param   array  $path                    Current property path.
     * @param   string $type                    Type of object to convert.
     * @param   object $lastObject              Internal use.
     * @param   array  $objectArrayPropertyPath Internal use.
     */
    protected function fetchDomainObjectsInternal($value, array $path, $type, $lastObject = null, array $objectArrayPropertyPath = [])
    {
        $propertyName = reset($path);
        $propertyValue = Core::get()->getObjectService()->getObjectProperty($value, $propertyName);

        if (null === $propertyValue) {
            return;
        }

        if (is_object($value)) {
            $lastObject = $value;
            $objectArrayPropertyPath = [$propertyName];
        } elseif (is_array($value)) {
            $objectArrayPropertyPath[] = $propertyName;
        }

        if (1 === count($path)) {
            $domainObject = $this->persistenceManager->getObjectByIdentifier($propertyValue, $type);

            $this->setDomainObjectInProperty($value, $propertyName, $domainObject, $lastObject, $objectArrayPropertyPath);
        } else {
            array_shift($path);

            $this->fetchDomainObjectsInternal($propertyValue, $path, $type, $lastObject, $objectArrayPropertyPath);
        }
    }

    /**
     * Will inject the domain object in the property, at the correct path.
     *
     * @param object|array $object
     * @param string       $propertyName
     * @param object       $domainObject
     * @param object       $lastObject
     * @param array        $objectArrayPropertyPath
     */
    protected function setDomainObjectInProperty($object, $propertyName, $domainObject, $lastObject, array $objectArrayPropertyPath)
    {
        if (is_object($object)) {
            ObjectAccess::setProperty($object, $propertyName, $domainObject);
        } elseif (is_array($object)) {
            $this->injectDomainObjectInLastObject($domainObject, $lastObject, $objectArrayPropertyPath);
        }
    }

    /**
     * @param object $domainObject
     * @param object $lastObject
     * @param array  $objectArrayPropertyPath
     */
    protected function injectDomainObjectInLastObject($domainObject, $lastObject, array $objectArrayPropertyPath)
    {
        $objectPropertyName = reset($objectArrayPropertyPath);
        $array = Core::get()->getObjectService()->getObjectProperty($lastObject, $objectPropertyName);

        if (false === is_array($array)) {
            return;
        }

        array_shift($objectArrayPropertyPath);

        $object = ArrayUtility::setValueByPath(
            $array,
            implode('.', $objectArrayPropertyPath),
            $domainObject
        );

        ObjectAccess::setProperty($lastObject, $objectPropertyName, $object);
    }

    /**
     * @param PersistenceManager $persistenceManager
     */
    public function injectPersistenceManager(PersistenceManager $persistenceManager)
    {
        $this->persistenceManager = $persistenceManager;
    }
}
