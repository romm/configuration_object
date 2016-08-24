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

namespace Romm\ConfigurationObject\Traits;

/**
 * This trait allows the usage of internal variables inside a class.
 *
 * Use it if you want to store dynamically custom vars in your class, then be
 * able to get it inside or outside the class scope.
 *
 * @internal This class was created to be used inside this extension, and may
 *           change at any moment, you should not use it in you own extensions!
 */
trait InternalVariablesTrait
{

    /**
     * @var array
     */
    protected $internalVars = [];

    /**
     * @param string $name  Name of the internal var.
     * @param mixed  $value Value of the internal var.
     */
    public function setInternalVar($name, $value)
    {
        $this->internalVars[(string)$name] = $value;
    }

    /**
     * @param string $name Name of the internal var.
     * @return bool
     */
    public function hasInternalVar($name)
    {
        return array_key_exists((string)$name, $this->internalVars);
    }

    /**
     * @param string $name Name of the internal var.
     * @return mixed
     */
    public function getInternalVar($name)
    {
        $result = null;
        if ($this->hasInternalVar($name)) {
            $result = $this->internalVars[(string)$name];
        }

        return $result;
    }

    /**
     * @param string $name Name of the internal var.
     */
    public function unsetInternalVar($name)
    {
        if ($this->hasInternalVar($name)) {
            unset($this->internalVars[(string)$name]);
        }
    }
}
