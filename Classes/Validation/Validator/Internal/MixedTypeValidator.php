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
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

/**
 * @internal
 */
class MixedTypeValidator extends AbstractValidator
{

    /**
     * The property of the object is a mixed type: the validator could not be
     * guessed in the validator resolver, because the property was not filled
     * yet.
     *
     * We have to build the resolver now for the given object and merge its
     * result with the result of this validator.
     *
     * @inheritdoc
     */
    public function isValid($object)
    {
        if (is_object($object)) {
            $validator = Core::get()->getValidatorResolver()
                ->getBaseValidatorConjunction(get_class($object));

            $this->result->merge($validator->validate($object));
        }
    }
}
