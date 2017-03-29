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
use Romm\ConfigurationObject\Reflection\ReflectionService;
use Romm\ConfigurationObject\Validation\Validator\Internal\MixedTypeCollectionValidator;
use TYPO3\CMS\Extbase\Reflection\ReflectionService as ExtbaseReflectionService;
use TYPO3\CMS\Extbase\Validation\Validator\CollectionValidator;

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
        return (CollectionValidator::class === $validatorType)
            ? parent::createValidator(MixedTypeCollectionValidator::class)
            : parent::createValidator($validatorType, $validatorOptions);
    }

    /**
     * @param ExtbaseReflectionService $reflectionService
     */
    public function injectReflectionService(ExtbaseReflectionService $reflectionService)
    {
        $this->reflectionService = Core::get()->getObjectManager()->get(ReflectionService::class);
    }
}
