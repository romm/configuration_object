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

namespace Romm\ConfigurationObject\Validation;

use Romm\ConfigurationObject\Core\Core;
use Romm\ConfigurationObject\Core\Service\ObjectService;
use Romm\ConfigurationObject\Service\Items\MixedTypes\MixedTypesInterface;
use Romm\ConfigurationObject\Service\Items\MixedTypes\MixedTypesService;
use Romm\ConfigurationObject\Validation\Validator\Internal\ConfigurationObjectValidator;
use Romm\ConfigurationObject\Validation\Validator\Internal\MixedTypeCollectionValidator;
use Romm\ConfigurationObject\Validation\Validator\Internal\MixedTypeValidator;
use TYPO3\CMS\Extbase\Reflection\ReflectionService as ExtbaseReflectionService;
use TYPO3\CMS\Extbase\Validation\Validator\CollectionValidator;
use TYPO3\CMS\Extbase\Validation\Validator\ConjunctionValidator;
use TYPO3\CMS\Extbase\Validation\Validator\GenericObjectValidator;
use TYPO3\CMS\Extbase\Validation\Validator\ObjectValidatorInterface;

/**
 * Customized validator resolver, which it mostly used to support the mixed
 * types.
 *
 * When an instance of validator is created, we check if the type of this
 * validator is `CollectionValidator`: in this case we use a custom one instead:
 * `MixedTypeCollectionValidator` which will support the mixed types feature.
 */
class ValidatorResolver extends \TYPO3\CMS\Extbase\Validation\ValidatorResolver
{

    /**
     * @var array
     */
    protected $baseValidatorConjunctionsWithChecks = [];

    /**
     * @var ObjectService
     */
    protected $objectService;

    /**
     * @var \Romm\ConfigurationObject\Reflection\ReflectionService
     */
    protected $reflectionService;

    /**
     * @inheritdoc
     */
    public function createValidator($validatorType, array $validatorOptions = [])
    {
        return (CollectionValidator::class === $validatorType)
            ? parent::createValidator(MixedTypeCollectionValidator::class)
            : parent::createValidator($validatorType, $validatorOptions);
    }

    /**
     * @inheritdoc
     */
    protected function buildBaseValidatorConjunction($indexKey, $targetClassName, array $validationGroups = [])
    {
        parent::buildBaseValidatorConjunction($indexKey, $targetClassName, $validationGroups);

        /*
         * The code below is DIRTY: in order to use `SilentExceptionInterface`
         * feature we need an extended version of the `GenericObjectValidator`,
         * but this is hardcoded in:
         * \TYPO3\CMS\Extbase\Validation\ValidatorResolver::buildBaseValidatorConjunction()
         *
         * Here we replace every `GenericObjectValidator` by our own instance.
         *
         * Please, do not try this at home.
         */
        /** @var ConjunctionValidator $conjunctionValidator */
        $conjunctionValidator = $this->baseValidatorConjunctions[$indexKey];

        foreach ($conjunctionValidator->getValidators() as $validator) {
            if ($validator instanceof GenericObjectValidator) {
                /*
                 * A full check is processed on the properties to check for
                 * mixed types, in which case a validator is added to these
                 * properties.
                 */
                $this->addMixedTypeValidators($targetClassName, $validator);

                /** @var ConfigurationObjectValidator $newValidator */
                $newValidator = $this->objectManager->get(ConfigurationObjectValidator::class, []);

                foreach ($validator->getPropertyValidators() as $propertyName => $propertyValidators) {
                    foreach ($propertyValidators as $propertyValidator) {
                        $newValidator->addPropertyValidator($propertyName, $propertyValidator);
                    }
                }

                // Replacing the old validator with the new one...
                $conjunctionValidator->removeValidator($validator);
                unset($validator);
                $conjunctionValidator->addValidator($newValidator);
            }
        }
    }

    /**
     * This function will list the properties of the given class, and filter on
     * the ones that do not have a validator assigned yet.
     *
     * @param string                 $targetClassName
     * @param GenericObjectValidator $validator
     */
    protected function addMixedTypeValidators($targetClassName, GenericObjectValidator $validator)
    {
        foreach ($this->reflectionService->getClassPropertyNames($targetClassName) as $property) {
            /*
             * If the property already is already bound to an object validator,
             * there is no need to do further checks.
             */
            if ($this->propertyHasObjectValidator($validator, $property)) {
                continue;
            }

            if ($this->propertyIsMixedType($targetClassName, $property)) {
                /*
                 * The property is mixed, a validator with the `mixedTypes`
                 * option is added, to delegate the validator resolving to
                 * later (when the property is actually filled).
                 */
                $objectValidator = $this->createValidator(MixedTypeValidator::class);

                $validator->addPropertyValidator($property, $objectValidator);
            }
        }
    }

    /**
     * Checks among the existing validators of the given property if it does
     * already has an object validator (can be several types, like the classes
     * `ObjectValidator` or `ConjunctionValidator`, as long as they implement
     * the interface `ObjectValidatorInterface`).
     *
     * If one is found, `true` is returned.
     *
     * @param GenericObjectValidator $validator
     * @param string                 $property
     * @return bool
     */
    protected function propertyHasObjectValidator(GenericObjectValidator $validator, $property)
    {
        $propertiesValidators = $validator->getPropertyValidators();

        if (isset($propertiesValidators[$property])) {
            foreach ($propertiesValidators[$property] as $validator) {
                if ($validator instanceof ObjectValidatorInterface) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Checks if the given property is a mixed type.
     *
     * First, we check if the type of the property is a class that implements
     * the interface `MixedTypesInterface`.
     *
     * If not, we check if the property has the annotation `@mixedTypesResolver`
     * with a class name that implements the interface `MixedTypesInterface`.
     *
     * If one was found, `true` is returned.
     *
     * @param string $className
     * @param string $property
     * @return bool
     */
    protected function propertyIsMixedType($className, $property)
    {
        $mixedType = false;

        $propertySchema = $this->reflectionService->getClassSchema($className)->getProperty($property);

        if ($this->classIsMixedType($propertySchema['type'])) {
            $mixedType = true;
        } else {
            if ($this->reflectionService->isPropertyTaggedWith($className, $property, MixedTypesService::PROPERTY_ANNOTATION_MIXED_TYPE)) {
                $tags = $this->reflectionService->getPropertyTagValues($className, $property, MixedTypesService::PROPERTY_ANNOTATION_MIXED_TYPE);
                $mixedTypeClassName = reset($tags);

                if ($this->classIsMixedType($mixedTypeClassName)) {
                    $mixedType = true;
                }
            }
        }

        return $mixedType;
    }

    /**
     * @param string $className
     * @return bool
     */
    protected function classIsMixedType($className)
    {
        return Core::get()->classExists($className)
            && array_key_exists(MixedTypesInterface::class, class_implements($className));
    }

    /**
     * @param ExtbaseReflectionService $reflectionService
     */
    public function injectReflectionService(ExtbaseReflectionService $reflectionService)
    {
        $this->reflectionService = Core::get()->getReflectionService();
    }

    /**
     * @param ObjectService $objectService
     */
    public function injectObjectService(ObjectService $objectService)
    {
        $this->objectService = $objectService;
    }
}
