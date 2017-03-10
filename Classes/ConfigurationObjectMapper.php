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
use TYPO3\CMS\Extbase\Reflection\ReflectionService;

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
     * @var ReflectionService
     */
    protected $reflectionService;

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
        $typeConverter = $this->getTypeConverter($source, $targetType, $configuration);
        $targetType = ltrim($typeConverter->getTargetTypeForSource($source, $targetType), '\\');

        if (Core::get()->classExists($targetType)) {
            $source = $this->handleDataPreProcessor($source, $targetType, $currentPropertyPath);
            $targetType = $this->handleMixedType($source, $targetType, $currentPropertyPath);

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
            $this->configurationObjectConversionDTO->setResult($result);
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

        // If the target is a class, we get its properties, else we assume the source should be converted.
        $properties = $this->getProperties($targetType);

        if (null === $properties) {
            $properties = $source;
        }

        foreach ($source as $propertyName => $propertyValue) {
            if (array_key_exists($propertyName, $properties)) {
                $targetPropertyType = $typeConverter->getTypeOfChildProperty($targetType, $propertyName, $configuration);
                $currentPropertyPath[] = $propertyName;

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

            $resolver = $mixedTypesService->getMixedTypesResolver($source, $targetType);
            $targetType = $resolver->getObjectType();
            $resolverResult = $resolver->getResult();

            if ($resolverResult->hasErrors()) {
                $targetType = MixedTypesResolver::OBJECT_TYPE_NONE;
                $this->messages->forProperty(implode('.', $currentPropertyPath))->merge($resolverResult);
            }
        }

        return $targetType;
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
        $typeConverter = null;

        /**
         * @see \Romm\ConfigurationObject\Reflection\ReflectionService
         */
        if ('[]' === substr($targetType, -2)) {
            $className = substr($targetType, 0, -2);

            if (Core::get()->classExists($className)) {
                $typeConverter = $this->objectManager->get(ArrayConverter::class);
            }
        }

        if (!$typeConverter) {
            $typeConverter = $this->findTypeConverter($source, $targetType, $configuration);

            if ($typeConverter instanceof ExtbaseArrayConverter
                || $this->parseCompositeType($targetType) === '\\ArrayObject'
                || $this->parseCompositeType($targetType) === 'array'
            ) {
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
     * Internal function which fetches the properties of a class, and stores
     * them in a local cache to improve performances.
     *
     * @param mixed $targetType
     * @return array|null
     */
    protected function getProperties($targetType)
    {
        if (false === isset($this->typeProperties[$targetType])) {
            $this->typeProperties[$targetType] = (Core::get()->classExists($targetType)) ?
                $this->reflectionService->getClassSchema($targetType)->getProperties() :
                null;
        }

        return $this->typeProperties[$targetType];
    }

    /**
     * @return ConfigurationObjectConverter
     */
    protected function getObjectConverter()
    {
        return $this->objectManager->get(ConfigurationObjectConverter::class);
    }

    /**
     * @param ReflectionService $reflectionService
     */
    public function injectReflectionService(ReflectionService $reflectionService)
    {
        $this->reflectionService = $reflectionService;
    }
}
