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

namespace Romm\ConfigurationObject\Service\Items\MixedTypes;

use Romm\ConfigurationObject\ConfigurationObjectInterface;
use Romm\ConfigurationObject\Service\AbstractService;

/**
 * This service allows the type of a configuration sub-object to be dynamic,
 * depending on arbitrary conditions which you can customize by implementing the
 * interface `MixedTypesInterface` in the classes which need this feature.
 */
class MixedTypesService extends AbstractService
{

    /**
     * Default resolver which is returned by the function
     * `getMixedTypesResolver()` if the given class name does not implement the
     * correct interface.
     *
     * It will prevent potentially thousands of processors created for nothing.
     *
     * @var MixedTypesResolver
     */
    protected $defaultResolver;

    /**
     * Initialization: will create the default processor.
     */
    public function initialize()
    {
        $this->defaultResolver = new MixedTypesResolver();
    }

    /**
     * @param mixed  $data
     * @param string $className Valid class name.
     * @return MixedTypesResolver
     */
    public function getMixedTypesResolver($data, $className)
    {
        $interfaces = class_implements($className);

        if (true === isset($interfaces[MixedTypesInterface::class])) {
            $resolver = new MixedTypesResolver();
            $resolver->setData($data);
            $resolver->setObjectType($className);

            /** @var MixedTypesInterface|ConfigurationObjectInterface $className */
            $className::getInstanceClassName($resolver);
        } else {
            $resolver = $this->defaultResolver;
            $resolver->setData($data);
            $resolver->setObjectType($className);
        }

        return $resolver;
    }
}
