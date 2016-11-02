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
 * @author Katsuhiro Ogawa <ko.fivestar@gmail.com>
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 */
class Config implements ConfigInterface
{
    protected $name;
    protected $description;
    protected $finder;
    protected $dir;
    protected $customFixers = array();
    protected $usingCache = true;
    protected $hideProgress = false;
    protected $cacheFile = '.php_cs.cache';
    protected $phpExecutable;
    protected $isRiskyAllowed = false;
    protected $rules = array('@PSR2' => true);

    public function __construct($name = 'default', $description = 'A default configuration')
    {
        $this->name = $name;
        $this->description = $description;
    }

    /**
     * @return static
     */
    public static function create()
    {
        return new static();
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheFile()
    {
        return $this->cacheFile;
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomFixers()
    {
        return $this->customFixers;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * {@inheritdoc}
     */
    public function getFinder()
    {
        if (null === $this->finder) {
            $this->finder = new Finder();
        }

        return $this->finder;
    }

    /**
     * {@inheritdoc}
     */
    public function getHideProgress()
    {
        return $this->hideProgress;
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
    public function getPhpExecutable()
    {
        return $this->phpExecutable;
    }

    /**
     * {@inheritdoc}
     */
    public function getRiskyAllowed()
    {
        return $this->isRiskyAllowed;
    }

    /**
     * {@inheritdoc}
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsingCache()
    {
        return $this->usingCache;
    }

    /**
     * {@inheritdoc}
     */
    public function registerCustomFixers($fixers)
    {
        if (false === is_array($fixers) && false === $fixers instanceof \Traversable) {
            throw new \InvalidArgumentException(sprintf(
                'Argument must be an array or a Traversable, got "%s".',
                is_object($fixers) ? get_class($fixers) : gettype($fixers)
            ));
        }

        foreach ($fixers as $fixer) {
            $this->addCustomFixer($fixer);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setCacheFile($cacheFile)
    {
        $this->cacheFile = $cacheFile;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setFinder($finder)
    {
        if (false === is_array($finder) && false === $finder instanceof \Traversable) {
            throw new \InvalidArgumentException(sprintf(
                'Argument must be an array or a Traversable, got "%s".',
                is_object($finder) ? get_class($finder) : gettype($finder)
            ));
        }

        $this->finder = $finder;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setHideProgress($hideProgress)
    {
        $this->hideProgress = $hideProgress;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setPhpExecutable($phpExecutable)
    {
        $this->phpExecutable = $phpExecutable;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setRiskyAllowed($isRiskyAllowed)
    {
        $this->isRiskyAllowed = $isRiskyAllowed;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setRules(array $rules)
    {
        $this->rules = $rules;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setUsingCache($usingCache)
    {
        $this->usingCache = $usingCache;

        return $this;
    }

    private function addCustomFixer(FixerInterface $fixer)
    {
        $this->customFixers[] = $fixer;
    }
}
