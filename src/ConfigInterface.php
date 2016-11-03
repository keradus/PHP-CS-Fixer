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
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 */
interface ConfigInterface
{
    /**
     * Returns the path to the cache file.
     *
     * @return string|null Returns null if not using cache
     */
    public function getCacheFile();

    /**
     * Returns the custom fixers to use.
     *
     * @return FixerInterface[]
     */
    public function getCustomFixers();

    /**
     * Returns files to scan.
     *
     * @return iterable|\Traversable|string[]
     */
    public function getFinder();

    /**
     * Returns true if progress should be hidden.
     *
     * @return bool
     */
    public function getHideProgress();

    /**
     * Returns the name of the configuration.
     *
     * The name must be all lowercase and without any spaces.
     *
     * @return string|null The name of the configuration
     */
    public function getName();

    /**
     * Get configured PHP executable, if any.
     *
     * @return string|null
     */
    public function getPhpExecutable();

    /**
     * Check if it is allowed to run risky fixers.
     *
     * @return bool
     */
    public function getRiskyAllowed();

    /**
     * Get rules.
     *
     * Keys of array are names of fixers/sets, values are true/false.
     *
     * @return array
     */
    public function getRules();

    /**
     * Returns true if caching should be enabled.
     *
     * @return bool
     */
    public function getUsingCache();

    /**
     * Adds a suite of custom fixers.
     *
     * @param iterable|\Traversable|FixerInterface[] $fixers
     */
    public function registerCustomFixers($fixers);

    /**
     * Sets the path to the cache file.
     *
     * @param string $cacheFile
     *
     * @return self
     */
    public function setCacheFile($cacheFile);

    /**
     * @param iterable|\Traversable|string[] $finder
     *
     * @return self
     */
    public function setFinder($finder);

    /**
     * @param bool $hideProgress
     *
     * @return self
     */
    public function setHideProgress($hideProgress);

    /**
     * Set PHP executable.
     *
     * @param string|null $phpExecutable
     *
     * @return self
     */
    public function setPhpExecutable($phpExecutable);

    /**
     * Set if it is allowed to run risky fixers.
     *
     * @param bool $isRiskyAllowed
     *
     * @return self
     */
    public function setRiskyAllowed($isRiskyAllowed);

    /**
     * Set rules.
     *
     * Keys of array are names of fixers or sets.
     * Value for set must be bool (turn it on or off).
     * Value for fixer may be bool (turn it on or off) or array of configuration
     * (turn it on and contains configuration for FixerInterface::configure method).
     *
     * @param array $rules
     *
     * @return self
     */
    public function setRules(array $rules);

    /**
     * @param bool $usingCache
     *
     * @return self
     */
    public function setUsingCache($usingCache);
}
