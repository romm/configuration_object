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

namespace Romm\ConfigurationObject\Service\Items\StoreConfigurationArray;

/**
 * @see \Romm\ConfigurationObject\Service\Items\StoreConfigurationArray\StoreConfigurationArrayService
 */
trait StoreConfigurationArrayTrait
{

    /**
     * @var array
     */
    protected $configurationArray = [];

    /**
     * @return  array
     */
    public function getConfigurationArray()
    {
        return $this->configurationArray;
    }

    /**
     * @param  array $configurationArray
     * @return array
     */
    public function setConfigurationArray(array $configurationArray)
    {
        return $this->configurationArray = $configurationArray;
    }
}
