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

namespace PhpCsFixer;

use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver as BaseOptionsResolver;

/**
 * @internal
 */
class OptionsResolver extends BaseOptionsResolver
{
    /**
     * @var string|null
     */
    private $rootConfigurationOption;

    /**
     * @var array
     */
    private $defaults = array();

    /**
     * @var string[]
     */
    private $descriptions = array();

    /**
     * @var array
     */
    private $allowedValues = array();

    /**
     * @var array
     */
    private $allowedTypes = array();

    /**
     * Maps list of tokens in the root configuration array to the given option.
     *
     * @param string|null $option
     *
     * @return $this
     *
     * @deprecated will be removed in 3.0
     */
    public function mapRootConfigurationTo($option)
    {
        if (!$this->isDefined($option)) {
            throw new UndefinedOptionsException(sprintf('The option "%s" does not exist.', $option));
        }

        $this->rootConfigurationOption = $option;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefault($option, $value)
    {
        parent::setDefault($option, $value);

        $this->defaults[$option] = $value;

        return $this;
    }

    /**
     * Returns the default value of an option.
     *
     * @param string $option The name of the option
     *
     * @throws UndefinedOptionsException when the option is not defined or has no default value
     *
     * @return mixed The default value of the option
     */
    public function getDefault($option)
    {
        if (!$this->isDefined($option)) {
            throw new UndefinedOptionsException(sprintf('The option "%s" does not exist.', $option));
        }

        if (!$this->hasDefault($option)) {
            throw new UndefinedOptionsException(sprintf('The option "%s" has no default value.', $option));
        }

        return $this->defaults[$option];
    }

    /**
     * Returns the default value of all options.
     *
     * @return array
     */
    public function getDefaults()
    {
        return $this->defaults;
    }

    /**
     * Sets the description of an option.
     *
     * @param string $option      The name of the option
     * @param string $description The description of the option
     *
     * @throws UndefinedOptionsException When the option is not defined
     *
     * @return $this
     */
    public function setDescription($option, $description)
    {
        if (!$this->isDefined($option)) {
            throw new UndefinedOptionsException(sprintf('The option "%s" does not exist.', $option));
        }

        $this->descriptions[$option] = $description;

        return $this;
    }

    /**
     * Returns the description of an option.
     *
     * @param string $option The name of the option
     *
     * @throws UndefinedOptionsException When the option is not defined
     *
     * @return string|null The default value of the option if set, null otherwise
     */
    public function getDescription($option)
    {
        if (!$this->isDefined($option)) {
            throw new UndefinedOptionsException(sprintf('The option "%s" does not exist.', $option));
        }

        if (!array_key_exists($option, $this->descriptions)) {
            return null;
        }

        return $this->descriptions[$option];
    }

    /**
     * {@inheritdoc}
     */
    public function setAllowedValues($option, $allowedValues = null)
    {
        parent::setAllowedValues($option, $allowedValues);

        $this->allowedValues[$option] = is_array($allowedValues) ? $allowedValues : array($allowedValues);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addAllowedValues($option, $allowedValues = null)
    {
        parent::addAllowedValues($option, $allowedValues);

        if (!is_array($allowedValues)) {
            $allowedValues = array($allowedValues);
        }

        if (!isset($this->allowedValues[$option])) {
            $this->allowedValues[$option] = $allowedValues;
        } else {
            $this->allowedValues[$option] = array_merge($this->allowedValues[$option], $allowedValues);
        }

        return $this;
    }

    /**
     * Returns the allowed values of an option.
     *
     * @param string $option
     *
     * @return array|null
     */
    public function getAllowedValues($option)
    {
        if (!$this->isDefined($option)) {
            throw new UndefinedOptionsException(sprintf('The option "%s" does not exist.', $option));
        }

        if (!array_key_exists($option, $this->allowedValues)) {
            return null;
        }

        return $this->allowedValues[$option];
    }

    /**
     * {@inheritdoc}
     */
    public function setAllowedTypes($option, $allowedTypes = null)
    {
        parent::setAllowedTypes($option, $allowedTypes);

        $this->allowedTypes[$option] = (array) $allowedTypes;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addAllowedTypes($option, $allowedTypes = null)
    {
        parent::addAllowedTypes($option, $allowedTypes);

        if (!isset($this->allowedTypes[$option])) {
            $this->allowedTypes[$option] = (array) $allowedTypes;
        } else {
            $this->allowedTypes[$option] = array_merge($this->allowedTypes[$option], (array) $allowedTypes);
        }

        return $this;
    }

    /**
     * Returns the allowed types of an option.
     *
     * @param string $option
     *
     * @return array|null
     */
    public function getAllowedTypes($option)
    {
        if (!$this->isDefined($option)) {
            throw new UndefinedOptionsException(sprintf('The option "%s" does not exist.', $option));
        }

        if (!array_key_exists($option, $this->allowedTypes)) {
            return null;
        }

        return $this->allowedTypes[$option];
    }

    /**
     * {@inheritdoc}
     */
    public function remove($optionNames)
    {
        parent::remove($optionNames);

        foreach ((array) $optionNames as $option) {
            unset(
                $this->defaults[$option],
                $this->descriptions[$option],
                $this->allowedValues[$option],
                $this->allowedTypes[$option]
            );

            if ($option === $this->rootConfigurationOption) {
                $this->rootConfigurationOption = null;
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        parent::clear();

        $this->rootConfigurationOption = null;
        $this->defaults = array();
        $this->descriptions = array();
        $this->allowedValues = array();
        $this->allowedTypes = array();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(array $options = array())
    {
        if (null !== $this->rootConfigurationOption && !array_key_exists($this->rootConfigurationOption, $options) && count($options)) {
            @trigger_error(sprintf(
                'Passing %1$s at the root of the configuration is deprecated and will not be supported in 3.0, use "%1$s" => array(...) option instead.',
                $this->rootConfigurationOption
            ), E_USER_DEPRECATED);

            $options = array($this->rootConfigurationOption => $options);
        }

        return parent::resolve($options);
    }
}
