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

use PhpCsFixer\ConfigurationException\RequiredFixerConfigurationException;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\Fixer\DescribedFixerInterface;
use PhpCsFixer\Fixer\FixerInterface;

/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 */
abstract class AbstractFixer implements FixerInterface, DescribedFixerInterface
{
    /**
     * @var WhitespacesFixerConfig
     */
    protected $whitespacesConfig;

    public function __construct()
    {
        if ($this instanceof ConfigurableFixerInterface) {
            try {
                $this->configure(null);
            } catch (RequiredFixerConfigurationException $e) {
                // ignore
            }
        }

        if ($this instanceof WhitespacesFixerConfigAwareInterface) {
            $this->whitespacesConfig = $this->getDefaultWhitespacesFixerConfig();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isRisky()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        $nameParts = explode('\\', get_called_class());
        $name = substr(end($nameParts), 0, -strlen('Fixer'));

        return Utils::camelCaseToUnderscore($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority()
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(\SplFileInfo $file)
    {
        return true;
    }

    public function setWhitespacesConfig(WhitespacesFixerConfig $config)
    {
        $this->whitespacesConfig = $config;
    }

    public function getDefinition()
    {
        return new ShortFixerDefinition(
            $this->getDescription()
        );
    }

    /**
     * Legacy, will be removed if proper description has been added to all fixers.
     *
     * @return string
     */
    abstract protected function getDescription();

    private function getDefaultWhitespacesFixerConfig()
    {
        static $defaultWhitespacesFixerConfig = null;

        if (null === $defaultWhitespacesFixerConfig) {
            $defaultWhitespacesFixerConfig = new WhitespacesFixerConfig('    ', "\n");
        }

        return $defaultWhitespacesFixerConfig;
    }
}
