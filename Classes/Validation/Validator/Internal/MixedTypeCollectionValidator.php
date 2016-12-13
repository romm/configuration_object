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

namespace Romm\ConfigurationObject\Validation\Validator\Internal;

use Romm\ConfigurationObject\Core\Core;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Validation\Validator\CollectionValidator;
use TYPO3\CMS\Extbase\Validation\Validator\ObjectValidatorInterface;

/**
 * @internal
 */
class MixedTypeCollectionValidator extends CollectionValidator
{

    /**
     * @inheritdoc
     */
    public function isValid($value)
    {
        if (null === $this->result
            && version_compare(VersionNumberUtility::getCurrentTypo3Version(), '7.6.0', '<')
        ) {
            $this->result = new Result;
        }

        foreach ($value as $index => $collectionElement) {
            $collectionElementValidator = Core::get()->getValidatorResolver()
                ->getBaseValidatorConjunctionWithMixedTypesCheck(get_class($collectionElement));

            $this->result->forProperty($index)->merge($collectionElementValidator->validate($collectionElement));

            if ($collectionElementValidator instanceof ObjectValidatorInterface) {
                if (null === $this->validatedInstancesContainer
                    && version_compare(VersionNumberUtility::getCurrentTypo3Version(), '7.6.0', '<')
                ) {
                    $this->validatedInstancesContainer = new \SplObjectStorage();
                }

                $collectionElementValidator->setValidatedInstancesContainer($this->validatedInstancesContainer);
            }
        }
    }
}
