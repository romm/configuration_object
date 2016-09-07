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

use Romm\ConfigurationObject\ConfigurationObjectInstance;

/**
 * Data transfer object used when a script wants to get a configuration object.
 *
 * @see \Romm\ConfigurationObject\ConfigurationObjectFactory::get()
 */
class GetConfigurationObjectDTO extends AbstractServiceDTO
{

    /**
     * @var array
     */
    protected $configurationObjectData = [];

    /**
     * @var ConfigurationObjectInstance
     */
    protected $result;

    /**
     * @return array
     */
    public function getConfigurationObjectData()
    {
        return $this->configurationObjectData;
    }

    /**
     * @param array $configurationObjectData
     * @return $this
     */
    public function setConfigurationObjectData($configurationObjectData)
    {
        $this->configurationObjectData = $configurationObjectData;

        return $this;
    }

    /**
     * @return ConfigurationObjectInstance
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param ConfigurationObjectInstance $result
     * @return $this
     */
    public function setResult(ConfigurationObjectInstance $result)
    {
        $this->result = $result;

        return $this;
    }
}
