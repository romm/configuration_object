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

namespace Romm\ConfigurationObject\Service\Items\StoreConfigurationArray;

use Romm\ConfigurationObject\Service\AbstractService;
use Romm\ConfigurationObject\Service\DataTransferObject\ConfigurationObjectConversionDTO;
use Romm\ConfigurationObject\Service\Event\ObjectConversionAfterServiceEventInterface;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * This service allows the usage of automatic configuration arrays. Meaning that
 * when a configuration object is created, the array containing the values of
 * its (sub)properties will be stored, for further usage.
 *
 * To use it, import the trait `StoreConfigurationArrayTrait` in your
 * configuration object class.
 */
class StoreConfigurationArrayService extends AbstractService implements ObjectConversionAfterServiceEventInterface, SingletonInterface
{

    /**
     * See class description.
     *
     * @param ConfigurationObjectConversionDTO $serviceDataTransferObject
     */
    public function objectConversionAfter(ConfigurationObjectConversionDTO $serviceDataTransferObject)
    {
        if (is_object($serviceDataTransferObject->getResult())
            && in_array(StoreConfigurationArrayTrait::class, class_uses($serviceDataTransferObject->getResult()))
        ) {
            $this->storeConfigurationArray($serviceDataTransferObject);
        }
    }

    /**
     * @param ConfigurationObjectConversionDTO $serviceDataTransferObject
     */
    protected function storeConfigurationArray(ConfigurationObjectConversionDTO $serviceDataTransferObject)
    {
        $configuration = is_array($serviceDataTransferObject->getSource())
            ? $serviceDataTransferObject->getSource()
            : [$serviceDataTransferObject->getSource()];
        /** @var StoreConfigurationArrayTrait $entity */
        $entity = $serviceDataTransferObject->getResult();
        $entity->setConfigurationArray($configuration);
    }
}
