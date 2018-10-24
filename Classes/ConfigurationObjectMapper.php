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

namespace Romm\ConfigurationObject;

use Romm\ConfigurationObject\Core\Core;
use Romm\ConfigurationObject\Core\Service\ReflectionService;
use Romm\ConfigurationObject\Service\DataTransferObject\ConfigurationObjectConversionDTO;
use Romm\ConfigurationObject\Service\DataTransferObject\GetTypeConverterDTO;
use Romm\ConfigurationObject\Service\Event\ObjectConversionAfterServiceEventInterface;
use Romm\ConfigurationObject\Service\Event\ObjectConversionBeforeServiceEventInterface;
use Romm\ConfigurationObject\Service\Items\DataPreProcessor\DataPreProcessorService;
use Romm\ConfigurationObject\Service\Items\MixedTypes\MixedTypesResolver;
use Romm\ConfigurationObject\Service\Items\MixedTypes\MixedTypesService;
use Romm\ConfigurationObject\Service\ServiceFactory;
use Romm\ConfigurationObject\Service\ServiceInterface;
use Romm\ConfigurationObject\TypeConverter\ArrayConverter;
use Romm\ConfigurationObject\TypeConverter\ConfigurationObjectConverter;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Property\Exception\TypeConverterException;
use TYPO3\CMS\Extbase\Property\PropertyMapper;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface;
use TYPO3\CMS\Extbase\Property\TypeConverter\ArrayConverter as ExtbaseArrayConverter;
use TYPO3\CMS\Extbase\Property\TypeConverter\ObjectConverter;
use TYPO3\CMS\Extbase\Property\TypeConverterInterface;
use TYPO3\CMS\Extbase\Reflection\PropertyReflection;

/**
 * Custom mapper used for configuration objects.
 *
 * The mapper will recursively go through all the object properties, and use a
 * correct type converter (fetched from the property reflection) to fill the
 * property with the given value.
 *
 * Note that this class inherits from the default Extbase `PropertyMapper`,
 * because existing functionality is still used.
 */
class ConfigurationObjectMapper extends PropertyMapper
{
    /**
     * Contains the initial called target type.
     *
     * @var string
     */
    protected $rootTargetType;

    /**
     * @var ServiceFactory
     */
    protected $serviceFactory;

    /**
     * @var ConfigurationObjectConversionDTO
     */
    protected $configurationObjectConversionDTO;

    /**
     * @var GetTypeConverterDTO
     */
    protected $getTypeConverterDTO;

    /**
     * @var array
     */
    protected $existingClassList = [];

    /**
     * @var array
     */
    protected $typeProperties = [];

    /**
     * @inheritdoc
     */
    public function convert($source, $targetType, PropertyMappingConfigurationInterface $configuration = null)
    {
        $this->rootTargetType = $targetType;
        $this->serviceFactory = ConfigurationObjectFactory::getInstance()
            ->getConfigurationObjectServiceFactory($targetType);

        $this->configurationObjectConversionDTO = new ConfigurationObjectConversionDTO($this->rootTargetType, $this->serviceFactory);
        $this->getTypeConverterDTO = new GetTypeConverterDTO($this->rootTargetType, $this->serviceFactory);

        $result = call_user_func_array(['parent', 'convert'], func_get_args());

        unset($this->configurationObjectConversionDTO);
        unset($this->getTypeConverterDTO);

        return $result;
    }

    /**
     * Will recursively fill all the properties of the configuration object.
     *
     * @inheritdoc
     */
    protected function doMapping($source, $targetType, PropertyMappingConfigurationInterface $configuration, &$currentPropertyPath)
    {
        if ($source === null) {
            return null;
        }

        $typeConverter = $this->getTypeConverter($source, $targetType, $configuration);
        $targetType = ltrim($typeConverter->getTargetTypeForSource($source, $targetType), '\\');

        if (Core::get()->classExists($targetType)) {
            $targetType = $this->handleMixedType($source, $targetType, $currentPropertyPath);
            $source = $this->handleDataPreProcessor($source, $targetType, $currentPropertyPath);

            if (MixedTypesResolver::OBJECT_TYPE_NONE === $targetType) {
                return null;
            }
        }

        $convertedChildProperties = (is_array($source))
            ? $this->convertChildProperties($source, $targetType, $typeConverter, $configuration, $currentPropertyPath)
            : [];

        $this->configurationObjectConversionDTO
            ->setSource($source)
            ->setTargetType($targetType)
            ->setConvertedChildProperties($convertedChildProperties)
            ->setCurrentPropertyPath($currentPropertyPath)
            ->setResult(null);
        $this->serviceFactory->runServicesFromEvent(ObjectConversionBeforeServiceEventInterface::class, 'objectConversionBefore', $this->configurationObjectConversionDTO);

        if (null === $this->configurationObjectConversionDTO->getResult()) {
            $result = $typeConverter->convertFrom($source, $targetType, $convertedChildProperties);
            if (null !== $this->configurationObjectConversionDTO) {
                $this->configurationObjectConversionDTO->setResult($result);
            }
        }

        $this->serviceFactory->runServicesFromEvent(ObjectConversionAfterServiceEventInterface::class, 'objectConversionAfter', $this->configurationObjectConversionDTO);
        $result = $this->configurationObjectConversionDTO->getResult();

        if ($result instanceof Error) {
            $this->messages
                ->forProperty(implode('.', $currentPropertyPath))
                ->addError($result);
        }

        return $result;
    }

    /**
     * Will convert all the properties of the given source, depending on the
     * target type.
     *
     * @param array                                 $source
     * @param string                                $targetType
     * @param TypeConverterInterface                $typeConverter
     * @param PropertyMappingConfigurationInterface $configuration
     * @param array                                 $currentPropertyPath
     * @return array
     */
    protected function convertChildProperties(array $source, $targetType, TypeConverterInterface $typeConverter, PropertyMappingConfigurationInterface $configuration, array &$currentPropertyPath)
    {
        $convertedChildProperties = [];
        $properties = $source;

        // If the target is a class, we get its properties, else we assume the source should be converted.
        if (Core::get()->classExists($targetType)) {
            $properties = $this->getProperties($targetType);
        }

        foreach ($source as $propertyName => $propertyValue) {
            if (array_key_exists($propertyName, $properties)) {
                $currentPropertyPath[] = $propertyName;
                $targetPropertyType = $typeConverter->getTypeOfChildProperty($targetType, $propertyName, $configuration);
                $targetPropertyTypeBis = $this->checkMixedTypeAnnotationForProperty($targetType, $propertyName, $targetPropertyType);
                $targetPropertyType = $targetPropertyTypeBis ?: $targetPropertyType;

                $targetPropertyValue = (null !== $targetPropertyType)
                    ? $this->doMapping($propertyValue, $targetPropertyType, $configuration, $currentPropertyPath)
                    : $propertyValue;

                array_pop($currentPropertyPath);

                if (false === $targetPropertyValue instanceof Error) {
                    $convertedChildProperties[$propertyName] = $targetPropertyValue;
                }
            }
        }

        return $convertedChildProperties;
    }

    /**
     * @param string $targetType
     * @param string $propertyName
     * @param string $propertyType
     * @return null|string
     */
    protected function checkMixedTypeAnnotationForProperty($targetType, $propertyName, $propertyType)
    {
        $result = null;

        if ($this->serviceFactory->has(ServiceInterface::SERVICE_MIXED_TYPES)) {
            /** @var MixedTypesService $mixedTypesService */
            $mixedTypesService = $this->serviceFactory->get(ServiceInterface::SERVICE_MIXED_TYPES);

            // Is the property composite?
            $isComposite = $this->parseCompositeType($propertyType) !== $propertyType;

            $result = $mixedTypesService->checkMixedTypeAnnotationForProperty($targetType, $propertyName, $isComposite);
        }

        return $result;
    }

    /**
     * Will check if the target type class inherits of `MixedTypeInterface`. If
     * so, it means the real type of the target must be fetched through the
     * function `getInstanceClassName()`.
     *
     * @param mixed $source
     * @param mixed $targetType
     * @param array $currentPropertyPath
     * @return ConfigurationObjectInterface
     */
    protected function handleMixedType($source, $targetType, $currentPropertyPath)
    {
        if ($this->serviceFactory->has(ServiceInterface::SERVICE_MIXED_TYPES)) {
            /** @var MixedTypesService $mixedTypesService */
            $mixedTypesService = $this->serviceFactory->get(ServiceInterface::SERVICE_MIXED_TYPES);

            if ($mixedTypesService->classIsMixedTypeResolver($targetType)) {
                $resolver = $mixedTypesService->getMixedTypesResolver($source, $targetType);
                $targetType = $resolver->getObjectType();
                $resolverResult = $resolver->getResult();

                if ($resolverResult->hasErrors()) {
                    $targetType = MixedTypesResolver::OBJECT_TYPE_NONE;
                    $this->messages->forProperty(implode('.', $currentPropertyPath))->merge($resolverResult);
                }
            }
        }

        return $targetType;
    }

    /**
     * Will check if the target type is a class, then call functions which will
     * check the interfaces of the class.
     *
     * @param mixed $source
     * @param mixed $targetType
     * @param array $currentPropertyPath
     * @return array
     */
    protected function handleDataPreProcessor($source, $targetType, $currentPropertyPath)
    {
        if ($this->serviceFactory->has(ServiceInterface::SERVICE_DATA_PRE_PROCESSOR)) {
            /** @var DataPreProcessorService $dataProcessorService */
            $dataProcessorService = $this->serviceFactory->get(ServiceInterface::SERVICE_DATA_PRE_PROCESSOR);

            $processor = $dataProcessorService->getDataPreProcessor($source, $targetType);
            $source = $processor->getData();
            $processorResult = $processor->getResult();

            if ($processorResult->hasErrors()) {
                $this->messages->forProperty(implode('.', $currentPropertyPath))->merge($processorResult);
            }
        }

        return $source;
    }

    /**
     * This function will fetch the type converter which will convert the source
     * to the requested target type.
     *
     * @param mixed $source
     * @param mixed $targetType
     * @param mixed $configuration
     * @return TypeConverterInterface
     * @throws TypeConverterException
     */
    protected function getTypeConverter($source, $targetType, $configuration)
    {
        $compositeType = $this->parseCompositeType($targetType);

        if (in_array($compositeType, ['\\ArrayObject', 'array'])) {
            $typeConverter = $this->objectManager->get(ArrayConverter::class);
        } else {
            $typeConverter = $this->findTypeConverter($source, $targetType, $configuration);

            if ($typeConverter instanceof ExtbaseArrayConverter) {
                $typeConverter = $this->objectManager->get(ArrayConverter::class);
            } elseif ($typeConverter instanceof ObjectConverter) {
                $typeConverter = $this->getObjectConverter();
            }
        }

        if (!is_object($typeConverter) || !$typeConverter instanceof TypeConverterInterface) {
            throw new TypeConverterException('Type converter for "' . $source . '" -> "' . $targetType . '" not found.');
        }

        return $typeConverter;
    }

    /**
     * @param string $compositeType
     * @return string
     */
    public function parseCompositeType($compositeType)
    {
        if ('[]' === substr($compositeType, -2)) {
            return '\\ArrayObject';
        } else {
            return parent::parseCompositeType($compositeType);
        }
    }

    /**
     * Internal function that fetches the properties of a class.
     *
     * @param $targetType
     * @return array
     */
    protected function getProperties($targetType)
    {
        $properties = ReflectionService::get()->getClassReflection($targetType)->getProperties();
        $propertiesKeys = array_map(
            function (PropertyReflection $propertyReflection) {
                return $propertyReflection->getName();
            },
            $properties
        );

        return array_combine($propertiesKeys, $properties);
    }

    /**
     * @return ConfigurationObjectConverter
     */
    protected function getObjectConverter()
    {
        return $this->objectManager->get(ConfigurationObjectConverter::class);
    }
}
