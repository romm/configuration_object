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

namespace Romm\ConfigurationObject\Tests\Fixture\Company;

use Romm\ConfigurationObject\Service\Items\DataPreProcessor\DataPreProcessor;
use Romm\ConfigurationObject\Service\Items\DataPreProcessor\DataPreProcessorInterface;
use Romm\ConfigurationObject\Service\Items\MixedTypes\MixedTypesInterface;
use Romm\ConfigurationObject\Service\Items\MixedTypes\MixedTypesResolver;
use Romm\ConfigurationObject\Traits\ConfigurationObject\MagicMethodsTrait;
use TYPO3\CMS\Extbase\Annotation\Validate;

/**
 * @method setName(string $name)
 * @method string getName()
 */
class Employee implements DataPreProcessorInterface, MixedTypesInterface
{
    use MagicMethodsTrait;

    const NAME_FOO = 'foo';
    const NAME_BAR = 'bar';

    const KEY_ANOTHER_EMPLOYEE = 'anotherEmployee';

    /**
     * @var string
     * @Validate("NotEmpty")
     * @Validate("Romm\ConfigurationObject\Tests\Fixture\Validator\WrongValueValidator")
     */
    protected $name;

    /**
     * @var string
     * @Validate("NotEmpty")
     * @Validate("Romm\ConfigurationObject\Validation\Validator\HasValuesValidator", options={"values": "Male|Female"})
     */
    protected $gender;

    /**
     * @var string
     * @Validate("NotEmpty")
     */
    protected $email;

    /**
     * @inheritdoc
     */
    public static function dataPreProcessor(DataPreProcessor $processor)
    {
        $data = $processor->getData();

        if (self::NAME_FOO === $data['name']) {
            $data['name'] = self::NAME_BAR;

            $processor->setData($data);
        }
    }

    /**
     * @inheritdoc
     */
    public static function getInstanceClassName(MixedTypesResolver $resolver)
    {
        $data = $resolver->getData();

        if (array_key_exists(self::KEY_ANOTHER_EMPLOYEE, $data)) {
            $resolver->setObjectType(AnotherEmployee::class);
        }
    }
}
