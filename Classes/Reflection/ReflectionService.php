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

namespace Romm\ConfigurationObject\Reflection;

use Romm\ConfigurationObject\Core\Core;

/**
 * This class is used to handle the common array annotation: `\Some\Class[]`.
 *
 * Indeed, TYPO3 does only support annotations like: `\ArrayObject<\Some\Class>`
 * but this is not well supported by IDEs.
 */
class ReflectionService extends \TYPO3\CMS\Extbase\Reflection\ReflectionService
{
    /**
     * @inheritdoc
     */
    public function getPropertyTagsValues($className, $propertyName)
    {
        $result = parent::getPropertyTagsValues($className, $propertyName);

        if (isset($result['var'])) {
            $result['var'] = $this->handleArrayAnnotation($result['var']);
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getPropertyTagValues($className, $propertyName, $tag)
    {
        $result = parent::getPropertyTagValues($className, $propertyName, $tag);

        if ($tag === 'var') {
            $result = $this->handleArrayAnnotation($result);
        }

        return $result;
    }

    /**
     * Will transform the annotation:
     * `\Some\Class[]` -> `\ArrayObject<\Some\Class>
     *
     * The last one is supported by TYPO3. See class description for more
     * information.
     *
     * @param array $varTags
     * @return array
     */
    protected function handleArrayAnnotation(array $varTags)
    {
        foreach ($varTags as $key => $tag) {
            if ('[]' === substr($tag, -2)) {
                $class = substr($tag, 0, -2);

                if (Core::get()->classExists($class)) {
                    $varTags[$key] = "\\ArrayObject<$class>";
                }
            }
        }

        return $varTags;
    }
}
