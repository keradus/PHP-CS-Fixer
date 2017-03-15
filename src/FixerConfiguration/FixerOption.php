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

final class FixerOption implements FixerOptionInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $description;

    /**
     * @var mixed
     */
    private $default;

    /**
     * @var bool
     */
    private $useDefault = false;

    /**
     * @var null|string[]
     */
    private $allowedTypes;

    /**
     * @var null|array
     */
    private $allowedValues;

    /**
     * @var \Closure|null
     */
    private $normalizer;

    /**
     * @param string $name
     * @param string $description
     */
    public function __construct($name, $description)
    {
        $this->name = $name;
        $this->description = $description;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $default
     *
     * @return $this
     */
    public function setDefault($default)
    {
        $this->default = $default;
        $this->useDefault = true;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasDefault()
    {
        return $this->useDefault;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefault()
    {
        if (!$this->hasDefault()) {
            throw new \LogicException('No default value defined.');
        }

        return $this->default;
    }

    /**
     * @param string[] $allowedTypes
     *
     * @return $this
     */
    public function setAllowedTypes(array $allowedTypes)
    {
        $this->allowedTypes = $allowedTypes;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedTypes()
    {
        return $this->allowedTypes;
    }

    /**
     * @param array $allowedValues
     *
     * @return $this
     */
    public function setAllowedValues(array $allowedValues)
    {
        foreach ($allowedValues as &$allowedValue) {
            if ($allowedValue instanceof \Closure) {
                $allowedValue = $this->unbind($allowedValue);
            }
        }

        $this->allowedValues = $allowedValues;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedValues()
    {
        return $this->allowedValues;
    }

    /**
     * @param \Closure $normalizer
     *
     * @return $this
     */
    public function setNormalizer(\Closure $normalizer)
    {
        $this->normalizer = $this->unbind($normalizer);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getNormalizer()
    {
        return $this->normalizer;
    }

    /**
     * Unbinds the given closure to avoid memory leaks.
     *
     * The closures provided to this class were probably defined in a fixer
     * class and thus bound to it by default. The configuration will then be
     * stored in {@see AbstractFixer::$configurationDefinition}, leading to the
     * following cyclic reference:
     *
     *     fixer -> configuration definition -> options -> closures -> fixer
     *
     * This cyclic reference prevent the garbage collector to free memory as
     * all elements are still referenced.
     *
     * See {@see https://bugs.php.net/bug.php?id=69639 Bug #69639} for details.
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
