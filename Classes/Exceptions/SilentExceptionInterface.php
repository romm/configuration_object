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

namespace Romm\ConfigurationObject\Exceptions;

/**
 * This interface can be implemented by exceptions sent in an object property
 * getter method.
 *
 * These exceptions may not block Configuration Object API processing but still
 * be thrown when actually using the object implementation.
 *
 * @see \Romm\ConfigurationObject\Core\Service\ObjectService::getObjectProperty()
 */
interface SilentExceptionInterface
{
}
