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

namespace Romm\ConfigurationObject\Service\DataTransferObject;

/**
 * Data transfer object used when a configuration object is being converted.
 *
 * @see \Romm\ConfigurationObject\ConfigurationObjectMapper::doMapping()
 */
class ConfigurationObjectConversionDTO extends AbstractServiceDTO
{

    /**
     * @var mixed
     */
    protected $source;

    /**
     * @var string
     */
    protected $targetType;

    /**
     * @var array
     */
    protected $convertedChildProperties;

    /**
     * @var array
     */
    protected $currentPropertyPath;

    /**
     * @var mixed
     */
    protected $result;

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param mixed $source
     * @return $this
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * @return string
     */
    public function getTargetType()
    {
        return $this->targetType;
    }

    /**
     * @param string $targetType
     * @return $this
     */
    public function setTargetType($targetType)
    {
        $this->targetType = $targetType;

        return $this;
    }

    /**
     * @return array
     */
    public function getConvertedChildProperties()
    {
        return $this->convertedChildProperties;
    }

    /**
     * @param array $convertedChildProperties
     * @return $this
     */
    public function setConvertedChildProperties($convertedChildProperties)
    {
        $this->convertedChildProperties = $convertedChildProperties;

        return $this;
    }

    /**
     * @return array
     */
    public function getCurrentPropertyPath()
    {
        return $this->currentPropertyPath;
    }

    /**
     * @param array $currentPropertyPath
     * @return $this
     */
    public function setCurrentPropertyPath($currentPropertyPath)
    {
        $this->currentPropertyPath = $currentPropertyPath;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param mixed $result
     * @return $this
     */
    public function setResult($result)
    {
        $this->result = $result;

        return $this;
    }
}
