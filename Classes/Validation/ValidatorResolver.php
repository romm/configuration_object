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

namespace Romm\ConfigurationObject\Validation;

use Romm\ConfigurationObject\Service\Items\MixedTypes\MixedTypesInterface;
use Romm\ConfigurationObject\Validation\Validator\Internal\MixedTypeCollectionValidator;
use Romm\ConfigurationObject\Validation\Validator\Internal\MixedTypeObjectValidator;
use TYPO3\CMS\Extbase\Validation\Validator\CollectionValidator;
use TYPO3\CMS\Extbase\Validation\Validator\ConjunctionValidator;

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
     * @inheritdoc
     */
    public function createValidator($validatorType, array $validatorOptions = [])
    {
        if (CollectionValidator::class === $validatorType) {
            $result = parent::createValidator(MixedTypeCollectionValidator::class);
        } else {
            $result = parent::createValidator($validatorType, $validatorOptions);
        }

        return $result;
    }

    /**
     * If the given class implements the interface `MixedTypesInterface`, a
     * custom conjunction validator is used instead of the default one (from the
     * parent class).
     *
     * @param string $targetClassName
     * @return ConjunctionValidator
     */
    public function getBaseValidatorConjunctionWithMixedTypesCheck($targetClassName)
    {
        if (false === array_key_exists($targetClassName, $this->baseValidatorConjunctionsWithChecks)) {
            $this->baseValidatorConjunctionsWithChecks[$targetClassName] = $this->buildBaseValidatorConjunctionWithMixedTypesCheck($targetClassName);
        }

        return $this->baseValidatorConjunctionsWithChecks[$targetClassName];
    }

    /**
     * @param string $targetClassName
     * @return ConjunctionValidator
     */
    protected function buildBaseValidatorConjunctionWithMixedTypesCheck($targetClassName)
    {
        $interfaces = class_implements($targetClassName);

        if (true === isset($interfaces[MixedTypesInterface::class])) {
            $conjunctionValidator = new ConjunctionValidator();
            $newValidator = new MixedTypeObjectValidator();
            $conjunctionValidator->addValidator($newValidator);
        } else {
            $conjunctionValidator = $this->getBaseValidatorConjunction($targetClassName);
        }

        return $conjunctionValidator;
    }
}
