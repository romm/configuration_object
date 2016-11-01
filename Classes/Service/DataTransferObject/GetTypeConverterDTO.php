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

use TYPO3\CMS\Extbase\Property\TypeConverterInterface;

/**
 * Data transfer object used when the configuration object mapper tries to
 * retrieve a type converter.
 *
 * @see \Romm\ConfigurationObject\ConfigurationObjectMapper::getTypeConverter()
 */
class GetTypeConverterDTO extends AbstractServiceDTO
{

    /**
     * @var TypeConverterInterface
     */
    protected $typeConverter;

    /**
     * @var mixed
     */
    protected $source;

    /**
     * @var string
     */
    protected $targetType;

    /**
     * @return TypeConverterInterface
     */
    public function getTypeConverter()
    {
        return $this->typeConverter;
    }

    /**
     * @param TypeConverterInterface $typeConverter
     * @return $this
     */
    public function setTypeConverter(TypeConverterInterface $typeConverter)
    {
        $this->typeConverter = $typeConverter;

        return $this;
    }

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
}
