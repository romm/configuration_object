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

namespace Romm\ConfigurationObject\Service\Event;

/**
 * A service event is an interface which can be implemented by any service.
 *
 * The service must then implement the interface functions, which will
 * automatically be detected and used during the configuration object creation
 * process.
 *
 * All service events functions have a single parameter: an instance of a
 * `AbstractServiceDTO` object, which you can interact with.
 *
 * This interface must be inherited by any service event which can be included
 * in the `ServiceFactory`.
 */
interface ServiceEventInterface
{

}
