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

namespace Romm\ConfigurationObject;

use Romm\ConfigurationObject\Core\Core;
use Romm\ConfigurationObject\Exceptions\Exception;
use Romm\ConfigurationObject\Traits\InternalVariablesTrait;
use TYPO3\CMS\Extbase\Error\Result;

/**
 * Root class of a configuration object. Contains both the converted object and
 * the validation result of this object.
 *
 * Hint: you should really check the validation result before even use your
 * configuration object: if it does contain errors, script should end and/or
 * inform the user what is wrong.
 *
 * Use: `$confObj->getValidationResult()->getFlattenedErrors()`
 */
class ConfigurationObjectInstance
{
    use InternalVariablesTrait;

    /**
     * @var Result
     */
    protected $validationResult;

    /**
     * @var ConfigurationObjectInterface
     */
    protected $object;

    /**
     * @var Result
     */
    private $mapperResult;

    /**
     * ConfigurationObject constructor.
     *
     * @param ConfigurationObjectInterface $objectInstance
     * @param Result                       $mapperResult
     */
    public function __construct(ConfigurationObjectInterface $objectInstance, Result $mapperResult)
    {
        $this->object = $objectInstance;
        $this->mapperResult = $mapperResult;
    }

    /**
     * Returns the real instance of the configuration object.
     *
     * Please note that you should always check the validation result before
     * even getting the object.
     * Use: `$myObject->getValidationResult()->hasErrors()`
     *
     * Warning: if you set `$forceInstance` to true, it means that the object
     * will be returned even if it contain errors, so be careful if you intend
     * to use it.
     *
     * @param bool $forceInstance If set to true, the instance will be returned even if the validation result contains errors. Should be used only if you know what you are doing.
     * @return ConfigurationObjectInterface
     * @throws Exception
     */
    public function getObject($forceInstance = false)
    {
        if (false === $forceInstance
            && true === $this->getValidationResult()->hasErrors()
        ) {
            throw new Exception(
                'Trying to access a configuration object which contains errors. You should first check if it is error free, then use it: "$yourObject->getValidationResult()->hasErrors()".',
                1471880442
            );
        }

        return $this->object;
    }

    /**
     * Returns the validation result of the configuration object.
     *
     * @return Result
     */
    public function getValidationResult()
    {
        if (false === $this->hasValidationResult()) {
            $this->refreshValidationResult();
        }

        return $this->validationResult;
    }

    /**
     * Manually set the validation result. Used in the cache service.
     *
     * Should only be internally used, please be careful if you need to use this
     * function.
     *
     * @param Result $result
     */
    public function setValidationResult(Result $result)
    {
        $this->validationResult = $result;
    }

    /**
     * Checks if the validation has already been done.
     *
     * @return bool
     */
    public function hasValidationResult()
    {
        return true === $this->validationResult instanceof Result;
    }

    /**
     * Sets up the validation result.
     *
     * @return $this
     */
    public function refreshValidationResult()
    {
        $validator = Core::get()->getValidatorResolver()
            ->getBaseValidatorConjunction(get_class($this->object));

        $this->validationResult = $validator->validate($this->object);
        $this->validationResult->merge($this->mapperResult);

        return $this;
    }
}
