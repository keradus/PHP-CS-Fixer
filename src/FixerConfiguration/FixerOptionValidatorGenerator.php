<?php

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace PhpCsFixer\FixerConfiguration;

/**
 * @internal
 */
final class FixerOptionValidatorGenerator
{
    /**
     * Sets the given option to only accept an array with a subset of the given values.
     *
     * @param array $allowedArrayValues
     *
     * @return \Closure
     */
    public function allowedValueIsSubsetOf(array $allowedArrayValues)
    {
        return $this->unbind(function ($values) use ($allowedArrayValues) {
            return empty(array_diff($values, $allowedArrayValues));
        });
    }

    /**
     * Unbinds the given closure to avoid memory leaks. See {@see https://bugs.php.net/bug.php?id=69639 Bug #69639} for
     * details.
     *
     * @param \Closure $closure
     *
     * @return \Closure
     */
    private function unbind(\Closure $closure)
    {
        if (PHP_VERSION_ID < 50400) {
            return $closure;
        }

        return $closure->bindTo(null);
    }
}
