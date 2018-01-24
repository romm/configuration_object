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

namespace Romm\ConfigurationObject\Tests\Unit\Service\Fixture;

use Romm\ConfigurationObject\Service\AbstractService;
use Romm\ConfigurationObject\Service\DataTransferObject\GetConfigurationObjectDTO;
use Romm\ConfigurationObject\Service\Event\ConfigurationObjectBeforeServiceEventInterface;

class DummyService extends AbstractService implements ConfigurationObjectBeforeServiceEventInterface
{
    const FOO_VALUE = 'bar';

    /**
     * @var string
     */
    protected static $foo;

    /**
     * @param GetConfigurationObjectDTO $serviceDataTransferObject
     */
    public function configurationObjectBefore(GetConfigurationObjectDTO $serviceDataTransferObject)
    {
        self::$foo = self::FOO_VALUE;
    }

    /**
     * @return string
     */
    public static function getFoo()
    {
        return self::$foo;
    }
}
