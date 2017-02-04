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
     * @var array
     */
    private $defaults = array();

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
     * {@inheritdoc}
     */
    public function remove($optionNames)
    {
        parent::remove($optionNames);

        foreach ((array) $optionNames as $option) {
            unset($this->defaults[$option]);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        parent::clear();

        $this->defaults = array();

        return $this;
    }
}
