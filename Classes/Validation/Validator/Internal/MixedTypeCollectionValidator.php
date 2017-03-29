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

namespace Romm\ConfigurationObject\Validation\Validator\Internal;

use Romm\ConfigurationObject\Core\Core;
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
        foreach ($value as $index => $collectionElement) {
            $collectionElementValidator = Core::get()->getValidatorResolver()
                ->getBaseValidatorConjunction(get_class($collectionElement));

            $this->result->forProperty($index)->merge($collectionElementValidator->validate($collectionElement));

            if ($collectionElementValidator instanceof ObjectValidatorInterface) {
                $collectionElementValidator->setValidatedInstancesContainer($this->validatedInstancesContainer);
            }
        }
    }
}
