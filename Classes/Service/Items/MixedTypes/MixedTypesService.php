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

namespace Romm\ConfigurationObject\Service\Items\MixedTypes;

use Romm\ConfigurationObject\Core\Core;
use Romm\ConfigurationObject\Core\Service\ReflectionService;
use Romm\ConfigurationObject\Exceptions\ClassNotFoundException;
use Romm\ConfigurationObject\Service\AbstractService;
use Romm\Formz\Exceptions\InvalidOptionValueException;

/**
 * This service allows the type of a configuration sub-object to be dynamic,
 * depending on arbitrary conditions which you can customize by implementing the
 * interface `MixedTypesInterface` in the classes which need this feature.
 */
class MixedTypesService extends AbstractService
{
    /**
     * The tag that can be added to a property (with a `@`).
     */
    const PROPERTY_ANNOTATION_MIXED_TYPE = 'mixedTypesResolver';

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
        if (true === $this->classIsMixedTypeResolver($className)) {
            $resolver = new MixedTypesResolver();
            $resolver->setData($data);
            $resolver->setObjectType($className);

            /** @var MixedTypesInterface $className */
            $className::getInstanceClassName($resolver);
        } else {
            $resolver = $this->defaultResolver;
            $resolver->setData($data);
            $resolver->setObjectType($className);
        }

        return $resolver;
    }

    /**
     * @param string $className
     * @return bool
     */
    public function classIsMixedTypeResolver($className)
    {
        return true === array_key_exists(MixedTypesInterface::class, class_implements($className));
    }

    /**
     * @param string $targetType
     * @param string $propertyName
     * @param string $isComposite
     * @return null|string
     * @throws ClassNotFoundException
     * @throws InvalidOptionValueException
     */
    public function checkMixedTypeAnnotationForProperty($targetType, $propertyName, $isComposite)
    {
        $result = null;

        if (Core::get()->classExists($targetType)) {
            $classReflection = ReflectionService::get()->getClassReflection($targetType);
            $propertyReflection = $classReflection->getProperty($propertyName);

            if ($propertyReflection->isTaggedWith(self::PROPERTY_ANNOTATION_MIXED_TYPE)) {
                $tag = $propertyReflection->getTagValues(self::PROPERTY_ANNOTATION_MIXED_TYPE);
                $className = trim(end($tag));

                if (false === Core::get()->classExists($className)) {
                    throw new ClassNotFoundException(
                        vsprintf(
                            'Class "%s" given as value for the tag "@%s" of the class property "%s::$%s" was not found.',
                            [$className, self::PROPERTY_ANNOTATION_MIXED_TYPE, $targetType, $propertyName]
                        ),
                        1489155862
                    );
                } else {
                    if (false === $this->classIsMixedTypeResolver($className)) {
                        throw new InvalidOptionValueException(
                            vsprintf(
                                'Class "%s" given as value for the tag "@%s" of the class property "%s::$%s" must implement the interface "%s".',
                                [$className, self::PROPERTY_ANNOTATION_MIXED_TYPE, $targetType, $propertyName, MixedTypesInterface::class]
                            ),
                            1489156005
                        );
                    }

                    $result = $className;

                    if (true === $isComposite) {
                        $result .= '[]';
                    }
                }
            }
        }

        return $result;
    }
}
