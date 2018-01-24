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

use Romm\ConfigurationObject\ConfigurationObjectInterface;
use Romm\ConfigurationObject\Service\ServiceFactory;
use Romm\ConfigurationObject\Service\ServiceInterface;
use Romm\ConfigurationObject\Traits\ConfigurationObject\MagicMethodsTrait;

/**
 * @method string getName()
 * @method Employee[] getEmployees()
 */
class Company implements ConfigurationObjectInterface
{
    use MagicMethodsTrait;

    /**
     * @var string
     * @validate TYPO3.CMS.Extbase:NotEmpty
     */
    protected $name;

    /**
     * @var \ArrayObject<Romm\ConfigurationObject\Tests\Fixture\Company\Employee>
     */
    protected $employees;

    /**
     * @inheritdoc
     */
    public static function getConfigurationObjectServices()
    {
        return ServiceFactory::getInstance()
            ->attach(ServiceInterface::SERVICE_DATA_PRE_PROCESSOR)
            ->attach(ServiceInterface::SERVICE_MIXED_TYPES);
    }
}
