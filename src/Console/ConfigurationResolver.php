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

namespace PhpCsFixer\Console;

use PhpCsFixer\Cache\CacheManagerInterface;
use PhpCsFixer\Cache\FileCacheManager;
use PhpCsFixer\Cache\FileHandler;
use PhpCsFixer\Cache\NullCacheManager;
use PhpCsFixer\Cache\Signature;
use PhpCsFixer\ConfigInterface;
use PhpCsFixer\ConfigurationException\InvalidConfigurationException;
use PhpCsFixer\Finder;
use PhpCsFixer\FixerFactory;
use PhpCsFixer\FixerInterface;
use PhpCsFixer\Linter\Linter;
use PhpCsFixer\Report\ReporterFactory;
use PhpCsFixer\RuleSet;
use PhpCsFixer\StdinFileInfo;
use PhpCsFixer\ToolInfo;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder as SymfonyFinder;

/**
 * The resolver that resolves configuration to use by command line options and config.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Katsuhiro Ogawa <ko.fivestar@gmail.com>
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 */
final class ConfigurationResolver
{
    const PATH_MODE_OVERRIDE = 'override';
    const PATH_MODE_INTERSECTION = 'intersection';

    /**
     * @var null|bool
     */
    private $allowRisky;

    /**
     * @var null|ConfigInterface
     */
    private $config;

    /**
     * @var null|string
     */
    private $configFile;

    /**
     * @var string
     */
    private $cwd;

    /**
     * @var ConfigInterface
     */
    private $defaultConfig;

    /**
     * @var null|ReporterInterface
     */
    private $reporter;

    /**
     * @var null|bool
     */
    private $isStdIn;

    /**
     * @var null|bool
     */
    private $isDryRun;

    /**
     * @var null|FixerInterface[]
     */
    private $fixers;

    /**
     * @var array
     */
    private $options = array(
        'allow-risky' => null,
        'config' => null,
        'dry-run' => null,
        'format' => 'txt',
        'path' => array(),
        'path-mode' => self::PATH_MODE_OVERRIDE,
        'progress' => null,
        'using-cache' => null,
        'cache-file' => null,
        'rules' => null,
        'diff' => null,
    );

    private $path;
    private $progress;
    private $usingCache;
    private $cacheFile;

    /**
     * @var RuleSet
     */
    private $ruleSet;
<<<<<<< 669a351aa0a753c43b050b1330dcfde069b89d60
=======

    private $formats = array();
>>>>>>> Configuration clean ups.
    private $finder;
    private $linter;
    private $cacheManager;

    public function __construct()
    {
    }

    /**
     * @return CacheManagerInterface
     */
    public function getCacheManager()
    {
        if (null === $this->cacheManager) {
            if ($this->getUsingCache() && (ToolInfo::isInstalledAsPhar() || ToolInfo::isInstalledByComposer())) {
                $this->cacheManager = new FileCacheManager(
                    new FileHandler($this->getCacheFile()),
                    new Signature(
                        PHP_VERSION,
                        ToolInfo::getVersion(),
                        $this->getRules()
                    ),
                    $this->isDryRun()
                );
            } else {
                $this->cacheManager = new NullCacheManager();
            }
        }

        return $this->cacheManager;
    }

    /**
     * Returns config instance.
     *
     * @return ConfigInterface
     */
    public function getConfig()
    {
        if (null === $this->config) {
            foreach ($this->computeConfigFiles() as $configFile) {
                if (!file_exists($configFile)) {
                    continue;
                }

                $config = include $configFile;

                // verify that the config has an instance of Config
                if (!$config instanceof ConfigInterface) {
                    throw new InvalidConfigurationException(sprintf('The config file: "%s" does not return a "PhpCsFixer\ConfigInterface" instance. Got: "%s".', $configFile, is_object($config) ? get_class($config) : gettype($config)));
                }

                $this->config = $config;
                $this->configFile = $configFile;

                break;
            }

            if (null === $this->config) {
                $this->config = $this->defaultConfig;
            }
        }

        return $this->config;
    }

    /**
     * Returns config file path.
     *
     * @return null|string
     */
    public function getConfigFile()
    {
        if (null === $this->configFile) {
            $this->getConfig();
        }

        return $this->configFile;
    }

    /**
     * Returns fixers.
     *
     * @return FixerInterface[] An array of FixerInterface
     */
    public function getFixers()
    {
        if (null === $this->fixers) {
            $fixerFactory = new FixerFactory();
            $fixerFactory->registerBuiltInFixers();
            $fixerFactory->registerCustomFixers($this->getConfig()->getCustomFixers());

            $this->fixers = $fixerFactory->useRuleSet($this->getRuleSet())->getFixers();

            if (false === $this->getRiskyAllowed()) {
                $riskyFixers = array_map(
                    function (FixerInterface $fixer) {
                        return $fixer->getName();
                    },
                    array_filter(
                        $this->fixers,
                        function (FixerInterface $fixer) {
                            return $fixer->isRisky();
                        }
                    )
                );

                if (!empty($riskyFixers)) {
                    throw new InvalidConfigurationException(sprintf('The rules contain risky fixers (%s), but they are not allowed to run. Perhaps you forget to use --allow-risky option?', implode(', ', $riskyFixers)));
                }
            }
        }

        return $this->fixers;
    }

    public function getReporter()
    {
        if (null === $this->reporter) {
            $reporterFactory = ReporterFactory::create();
            $reporterFactory->registerBuiltInReporters();

            if (array_key_exists('format', $this->options)) {
                $format = $this->options['format'];
            } elseif (method_exists($this->getConfig(), 'getFormat')) {
                // TODO: fix interface
                $format = $this->getConfig()->getFormat();
            } else {
                $format = 'txt'; // default
            }

            try {
                $this->reporter = $reporterFactory->getReporter($format);
            } catch (\UnexpectedValueException $e) {
                throw new InvalidConfigurationException(sprintf('The format "%s" is not defined, supported are %s.', $format, implode(', ', $formats)));
            }
        }

        return $this->reporter;
    }

    /**
     * Returns path.
     *
     * @return string[]
     */
    public function getPath()
    {
        if (null === $this->path) {
            $filesystem = new Filesystem();
            $cwd = $this->cwd;

            if (1 === count($this->options['path']) && '-' === $this->options['path'][0]) {
                $this->path = $this->options['path'];
            } else {
                $this->path = array_map(
                    function ($path) use ($cwd, $filesystem) {
                        $absolutePath = $filesystem->isAbsolutePath($path)
                            ? $path
                            : $cwd.DIRECTORY_SEPARATOR.$path;

                        if (!file_exists($absolutePath)) {
                            throw new InvalidConfigurationException(sprintf(
                                'The path "%s" is not readable.',
                                $path
                            ));
                        }

                        return $absolutePath;
                    },
                    $this->options['path']
                );
            }
        }

        return $this->path;
    }

    /**
     * Returns progress flag.
     *
     * @return bool
     */
    public function getProgress()
    {
        if (null === $this->progress) {
            $this->progress = $this->options['progress'] && !$this->getConfig()->getHideProgress();
        }

        return $this->progress;
    }

    /**
     * Returns rules.
     *
     * @return array
     */
    public function getRules()
    {
        return $this->getRuleSet()->getRules();
    }

    /**
     * Returns dry-run flag.
     *
     * @return bool
     */
    public function isDryRun()
    {
        if (null === $this->isDryRun) {
            if ($this->isStdIn()) {
                // Can't write to STDIN
                $this->isDryRun = true;
            } else {
                $this->isDryRun = $this->options['dry-run'];
            }
        }

        return $this->isDryRun;
    }

    /**
     * Set current working directory.
     *
     * @param string $cwd
     *
     * @return ConfigurationResolver
     */
    public function setCwd($cwd)
    {
        $this->cwd = $cwd;

        return $this;
    }

    /**
     * Set default config instance.
     *
     * @param ConfigInterface $config
     *
     * @return ConfigurationResolver
     */
    public function setDefaultConfig(ConfigInterface $config)
    {
        $this->defaultConfig = $config;

        return $this;
    }

    /**
     * Set option that will be resolved.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return ConfigurationResolver
     */
    public function setOption($name, $value)
    {
        if (!array_key_exists($name, $this->options)) {
            throw new InvalidConfigurationException(sprintf('Unknown option name: "%s".', $name));
        }

        $this->options[$name] = $value;

        return $this;
    }

    /**
     * Set options that will be resolved.
     *
     * @param array $options
     *
     * @return ConfigurationResolver
     */
    public function setOptions(array $options)
    {
        foreach ($options as $name => $value) {
            $this->setOption($name, $value);
        }

        return $this;
    }

    public function getUsingCache()
    {
        if (null === $this->usingCache) {
            if (null !== $this->options['using-cache']) {
                $this->usingCache = 'yes' === $this->options['using-cache'];
            } else {
                $this->usingCache = $this->getConfig()->getUsingCache();
            }
        }

        return $this->usingCache;
    }

    public function getCacheFile()
    {
        if (null === $this->cacheFile) {
            if (null !== $this->options['cache-file']) {
                $this->cacheFile = $this->options['cache-file'];
            } else {
                $this->cacheFile = $this->getConfig()->getCacheFile();
            }
        }

        return $this->cacheFile;
    }

    public function getRiskyAllowed()
    {
        if (null === $this->allowRisky) {
            if (null !== $this->options['allow-risky']) {
                $this->allowRisky = 'yes' === $this->options['allow-risky'];
            } else {
                $this->allowRisky = $this->getConfig()->getRiskyAllowed();
            }
        }

        return $this->allowRisky;
    }

    public function getFinder()
    {
        if (null === $this->finder) {
            $this->finder = $this->resolveFinder();
        }

        return $this->finder;
    }

    public function getLinter()
    {
        if (null === $this->linter) {
            $this->linter = new Linter($this->getConfig()->getPhpExecutable());
        }

        return $this->linter;
    }

    private function getRuleSet()
    {
        if (null === $this->ruleSet) {
            $this->ruleSet = new RuleSet($this->parseRules());
        }

        return $this->ruleSet;
    }

    /**
     * Compute file candidates for config file.
     *
     * @return string[]
     */
    private function computeConfigFiles()
    {
        $configFile = $this->options['config'];

        if (null !== $configFile) {
            if (false === file_exists($configFile) || false === is_readable($configFile)) {
                throw new InvalidConfigurationException(sprintf('Cannot read config file "%s".', $configFile));
            }

            return array($configFile);
        }

        $path = $this->getPath();

        if ($this->isStdIn() || 0 === count($path)) {
            $configDir = $this->cwd;
        } elseif (1 < count($path)) {
            throw new InvalidConfigurationException('For multiple paths config parameter is required.');
        } elseif (is_file($path[0]) && $dirName = pathinfo($path[0], PATHINFO_DIRNAME)) {
            $configDir = $dirName;
        } else {
            $configDir = $path[0];
        }

        $candidates = array(
            $configDir.DIRECTORY_SEPARATOR.'.php_cs',
            $configDir.DIRECTORY_SEPARATOR.'.php_cs.dist',
        );

        if ($configDir !== $this->cwd) {
            $candidates[] = $this->cwd.DIRECTORY_SEPARATOR.'.php_cs';
            $candidates[] = $this->cwd.DIRECTORY_SEPARATOR.'.php_cs.dist';
        }

        return $candidates;
    }

    /**
     * Compute rules.
     *
     * @return array
     */
    private function parseRules()
    {
        if (null === $this->options['rules']) {
            return $this->getConfig()->getRules();
        }

        $rules = array();

        foreach (array_map('trim', explode(',', $this->options['rules'])) as $rule) {
            if ('' === $rule) {
                throw new InvalidConfigurationException('Empty rule name is not allowed.');
            }

            if ('-' === $rule[0]) {
                $rules[substr($rule, 1)] = false;
            } else {
                $rules[$rule] = true;
            }
        }

        return $rules;
    }

    /**
     * Apply path on config instance.
     */
    private function resolveFinder()
    {
        if ($this->isStdIn()) {
            return new \ArrayIterator(array(new StdinFileInfo()));
        }

        $modes = array(self::PATH_MODE_OVERRIDE, self::PATH_MODE_INTERSECTION);

        if (!in_array(
            $this->options['path-mode'],
            $modes,
            true
        )) {
            throw new InvalidConfigurationException(sprintf(
                'The path-mode "%s" is not defined, supported are %s.',
                $this->options['path-mode'],
                implode(', ', $modes)
            ));
        }

        $isIntersectionPathMode = self::PATH_MODE_INTERSECTION === $this->options['path-mode'];

        $paths = array_filter(array_map(
            function ($path) {
                return realpath($path);
            },
            $this->getPath()
        ));

        if (!count($paths)) {
            if ($isIntersectionPathMode) {
                return new \ArrayIterator(array());
            }

            return $this->iterableToTraversable($this->getConfig()->getFinder());
        }

        $pathsByType = array(
            'file' => array(),
            'dir' => array(),
        );

        foreach ($paths as $path) {
            if (is_file($path)) {
                $pathsByType['file'][] = $path;
            } else {
                $pathsByType['dir'][] = $path.DIRECTORY_SEPARATOR;
            }
        }

        $nestedFinder = null;
        $iterator = null;
        $currentFinder = $this->iterableToTraversable($this->getConfig()->getFinder());

        try {
            $nestedFinder = $currentFinder instanceof \IteratorAggregate ? $currentFinder->getIterator() : $currentFinder;
        } catch (\Exception $e) {
        }

        if ($isIntersectionPathMode) {
            if (null === $nestedFinder) {
                throw new InvalidConfigurationException(
                    'Cannot create intersection with not-fully defined Finder in configuration file.'
                );
            }

            return new \CallbackFilterIterator(
                $nestedFinder,
                function (\SplFileInfo $current) use ($pathsByType) {
                    $currentRealPath = $current->getRealPath();

                    if (in_array($currentRealPath, $pathsByType['file'], true)) {
                        return true;
                    }

                    foreach ($pathsByType['dir'] as $path) {
                        if (0 === strpos($currentRealPath, $path)) {
                            return true;
                        }
                    }

                    return false;
                }
            );
        }

<<<<<<< 669a351aa0a753c43b050b1330dcfde069b89d60
        if ($currentFinder instanceof SymfonyFinder && null === $nestedFinder) {
            // finder from configuration Symfony finder and it is not fully defined, we may fulfill it
            return $currentFinder->in($pathsByType['dir'])->append($pathsByType['file']);
=======
        $this->finder = $iterator;
    }

    /**
     * Resolve fixers to run based on rules.
     */
    private function resolveFixers()
    {
        $this->fixers = $this->fixerFactory->useRuleSet($this->ruleSet)->getFixers();

        if (true === $this->allowRisky) {
            return;
        }

        $riskyFixers = array_map(
            function (FixerInterface $fixer) {
                return $fixer->getName();
            },
            array_filter(
                $this->fixers,
                function (FixerInterface $fixer) {
                    return $fixer->isRisky();
                }
            )
        );

        if (count($riskyFixers)) {
            throw new InvalidConfigurationException(sprintf('The rules contain risky fixers (%s), but they are not allowed to run. Perhaps you forget to use --allow-risky option?', implode(', ', $riskyFixers)));
        }
    }

    /**
     * Resolve isDryRun based on isStdIn property and dry-run option.
     */
    private function resolveIsDryRun()
    {
        // Can't write to STDIN
        if ($this->isStdIn) {
            $this->isDryRun = true;

            return;
        }

        $this->isDryRun = $this->options['dry-run'];
    }

    /**
     * Resolve isStdIn based on path option.
     */
    private function resolveIsStdIn()
    {
        $this->isStdIn = 1 === count($this->options['path']) && '-' === $this->options['path'][0];
    }

    private function resolvePathMode()
    {
        $modes = array(self::PATH_MODE_OVERRIDE, self::PATH_MODE_INTERSECTION);

        if (!in_array(
            $this->options['path-mode'],
            $modes,
            true
        )) {
            throw new InvalidConfigurationException(sprintf(
                'The path-mode "%s" is not defined, supported are %s.',
                $this->options['path-mode'],
                implode(', ', $modes)
            ));
        }
    }

    /**
     * Resolve path based on path option.
     */
    private function resolvePath()
    {
        $filesystem = new Filesystem();
        $cwd = $this->cwd;

        if (1 === count($this->options['path']) && '-' === $this->options['path'][0]) {
            $this->path = $this->options['path'];

            return;
        }

        $this->path = array_map(
            function ($path) use ($cwd, $filesystem) {
                $absolutePath = $filesystem->isAbsolutePath($path)
                    ? $path
                    : $cwd.DIRECTORY_SEPARATOR.$path
                ;

                if (!file_exists($absolutePath)) {
                    throw new InvalidConfigurationException(sprintf(
                        'The path "%s" is not readable.',
                        $path
                    ));
                }

                return $absolutePath;
            },
            $this->options['path']
        );
    }

    /**
     * Resolve progress based on progress option and config instance.
     */
    private function resolveProgress()
    {
        $this->progress = $this->options['progress'] && !$this->config->getHideProgress();
    }

    /**
     * Resolve rules.
     */
    private function resolveRules()
    {
        $this->ruleSet = new RuleSet($this->parseRules());
    }

    /**
     * Resolve using cache.
     */
    private function resolveUsingCache()
    {
        if (null !== $this->options['using-cache']) {
            $this->usingCache = 'yes' === $this->options['using-cache'];

            return;
        }

        $this->usingCache = $this->config->getUsingCache();
    }

    /**
     * Resolves cache file.
     */
    private function resolveCacheFile()
    {
        if (null !== $this->options['cache-file']) {
            $this->cacheFile = $this->options['cache-file'];

            return;
>>>>>>> Configuration clean ups.
        }

        return Finder::create()->in($pathsByType['dir'])->append($pathsByType['file']);
    }

    private function isStdIn()
    {
        if (null === $this->isStdIn) {
            $this->isStdIn = 1 === count($this->options['path']) && '-' === $this->options['path'][0];
        }

        return $this->isStdIn;
    }

    /**
     * @param iterable|array $iterable
     *
     * @return \Traversable
     */
    private function iterableToTraversable($iterable)
    {
        return is_array($iterable) ? new \ArrayIterator($iterable) : $iterable;
    }
}
