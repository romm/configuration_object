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

namespace Romm\ConfigurationObject\Service\Event;

use Romm\ConfigurationObject\Service\DataTransferObject\ConfigurationObjectConversionDTO;

/**
 * @see \Romm\ConfigurationObject\Service\Event\ServiceEvent
 */
interface ObjectConversionAfterServiceEventInterface extends ServiceEventInterface
{

    /**
     * @param ConfigurationObjectConversionDTO $serviceDataTransferObject
     * @return void
     */
    public function objectConversionAfter(ConfigurationObjectConversionDTO $serviceDataTransferObject);
}
