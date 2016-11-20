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

/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 */
final class FixerDescription
{
    private $riskyDescription;
    private $configurationDescription;
    private $defaultConfiguration;
    private $codeSamples;

    /**
     * @param array       $codeSamples              array of samples, where single sample is [code, configuration]
     * @param null|string $configurationDescription null for non-configurable fixer
     * @param null|array  $defaultConfiguration     null for non-configurable fixer
     * @param null|string $riskyDescription         null for non-risky fixer
     */
    public function __construct(
        array $codeSamples,
        $configurationDescription,
        array $defaultConfiguration = null,
        $riskyDescription
    ) {
        $this->codeSamples = $codeSamples;
        $this->configurationDescription = $configurationDescription;
        $this->defaultConfiguration = $defaultConfiguration;
        $this->riskyDescription = $riskyDescription;
    }

    public function getConfigurationDescription()
    {
        return $this->configurationDescription;
    }

    public function getDefaultConfiguration()
    {
        return $this->defaultConfiguration;
    }

    public function getRiskyDescription()
    {
        return $this->riskyDescription;
    }

    public function getCodeSamples()
    {
        return $this->codeSamples;
    }
}
