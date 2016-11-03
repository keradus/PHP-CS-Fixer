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

namespace PhpCsFixer\Tests\Console;

use PhpCsFixer\Config;
use PhpCsFixer\Console\ConfigurationResolver;
use PhpCsFixer\Finder;
use PhpCsFixer\Test\AccessibleObject;

/**
 * @author Katsuhiro Ogawa <ko.fivestar@gmail.com>
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 */
final class ConfigurationResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var ConfigurationResolver
     */
    protected $resolver;

    protected function setUp()
    {
        $this->config = new Config();
        $this->resolver = new ConfigurationResolver();
        $this->resolver
            ->setDefaultConfig($this->config)
        ;
    }

    protected function tearDown()
    {
        unset(
            $this->config,
            $this->resolver
        );
    }

    public function testSetOption()
    {
        $this->resolver->setOption('path', array('.'));
        $property = AccessibleObject::create($this->resolver)->options;

        $this->assertSame(array('.'), $property['path']);
    }

    /**
     * @expectedException              \PhpCsFixer\ConfigurationException\InvalidConfigurationException
     * @expectedExceptionMessageRegExp /^Unknown option name: "foo"\.$/
     */
    public function testSetOptionWithUndefinedOption()
    {
        $this->resolver->setOption('foo', 'bar');
    }

    public function testSetOptions()
    {
        $this->resolver->setOptions(array(
            'path' => '.',
            'config' => 'config.php_cs',
        ));
        $property = AccessibleObject::create($this->resolver)->options;

        $this->assertSame('.', $property['path']);
        $this->assertSame('config.php_cs', $property['config']);
    }

    public function testCwd()
    {
        $this->resolver->setCwd('foo');
        $property = AccessibleObject::create($this->resolver)->cwd;

        $this->assertSame('foo', $property);
    }

    public function testResolveProgressWithPositiveConfigAndPositiveOption()
    {
        $this->config->setHideProgress(true);
        $this->resolver
            ->setOption('progress', true)
        ;

        $this->assertFalse($this->resolver->getProgress());
    }

    public function testResolveProgressWithPositiveConfigAndNegativeOption()
    {
        $this->config->setHideProgress(true);
        $this->resolver
            ->setOption('progress', false)
        ;

        $this->assertFalse($this->resolver->getProgress());
    }

    public function testResolveProgressWithNegativeConfigAndPositiveOption()
    {
        $this->config->setHideProgress(false);
        $this->resolver
            ->setOption('progress', true)
        ;

        $this->assertTrue($this->resolver->getProgress());
    }

    public function testResolveProgressWithNegativeConfigAndNegativeOption()
    {
        $this->config->setHideProgress(false);
        $this->resolver
            ->setOption('progress', false)
        ;

        $this->assertFalse($this->resolver->getProgress());
    }

    public function testResolveConfigFileDefault()
    {
        $this->assertNull($this->resolver->getConfigFile());
        $this->assertInstanceOf('\\PhpCsFixer\\ConfigInterface', $this->resolver->getConfig());
    }

    public function testResolveConfigFileByPathOfFile()
    {
        $dir = __DIR__.'/../Fixtures/ConfigurationResolverConfigFile/case_1';

        $this->resolver
            ->setOption('path', array($dir.DIRECTORY_SEPARATOR.'foo.php'));

        $this->assertSame($dir.DIRECTORY_SEPARATOR.'.php_cs.dist', $this->resolver->getConfigFile());
        $this->assertInstanceOf('Test1Config', $this->resolver->getConfig());
    }

    public function testResolveConfigFileSpecified()
    {
        $file = __DIR__.'/../Fixtures/ConfigurationResolverConfigFile/case_4/my.php_cs';

        $this->resolver
            ->setOption('config', $file);

        $this->assertSame($file, $this->resolver->getConfigFile());
        $this->assertInstanceOf('Test4Config', $this->resolver->getConfig());
    }

    /**
     * @dataProvider provideResolveConfigFileDefaultCases
     */
    public function testResolveConfigFileChooseFile($expectedFile, $expectedClass, $path, $cwdPath = null)
    {
        $resolver = $this->resolver
            ->setOption('path', array($path))
        ;

        if (null !== $cwdPath) {
            $resolver->setCwd($cwdPath);
        }

        $this->assertSame($expectedFile, $this->resolver->getConfigFile());
        $this->assertInstanceOf($expectedClass, $this->resolver->getConfig());
    }

    public function provideResolveConfigFileDefaultCases()
    {
        $dirBase = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'Fixtures'.DIRECTORY_SEPARATOR.'ConfigurationResolverConfigFile'.DIRECTORY_SEPARATOR;

        return array(
            array(
                $dirBase.'case_1'.DIRECTORY_SEPARATOR.'.php_cs.dist',
                'Test1Config',
                $dirBase.'case_1',
            ),
            array(
                $dirBase.'case_2'.DIRECTORY_SEPARATOR.'.php_cs',
                'Test2Config',
                $dirBase.'case_2',
            ),
            array(
                $dirBase.'case_3'.DIRECTORY_SEPARATOR.'.php_cs',
                'Test3Config',
                $dirBase.'case_3',
            ),
            array(
                $dirBase.'case_6'.DIRECTORY_SEPARATOR.'.php_cs.dist',
                'Test6Config',
                $dirBase.'case_6'.DIRECTORY_SEPARATOR.'subdir',
                $dirBase.'case_6',
            ),
            array(
                $dirBase.'case_6'.DIRECTORY_SEPARATOR.'.php_cs.dist',
                'Test6Config',
                $dirBase.'case_6'.DIRECTORY_SEPARATOR.'subdir/empty_file.php',
                $dirBase.'case_6',
            ),
        );
    }

    /**
     * @expectedException              \PhpCsFixer\ConfigurationException\InvalidConfigurationException
     * @expectedExceptionMessageRegExp /^The config file: ".+[\/\\]Fixtures[\/\\]ConfigurationResolverConfigFile[\/\\]case_5[\/\\].php_cs.dist" does not return a "PhpCsFixer\\ConfigInterface" instance\. Got: "string"\.$/
     */
    public function testResolveConfigFileChooseFileWithInvalidFile()
    {
        $dirBase = realpath(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'Fixtures'.DIRECTORY_SEPARATOR.'ConfigurationResolverConfigFile'.DIRECTORY_SEPARATOR);
        $this->resolver
            ->setOption('path', array($dirBase.'/case_5'));
        $this->resolver->getConfig();
    }

    /**
     * @expectedException              \PhpCsFixer\ConfigurationException\InvalidConfigurationException
     * @expectedExceptionMessageRegExp /^For multiple paths config parameter is required.$/
     */
    public function testResolveConfigFileChooseFileWithPathArrayWithoutConfig()
    {
        $dirBase = realpath(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'Fixtures'.DIRECTORY_SEPARATOR.'ConfigurationResolverConfigFile'.DIRECTORY_SEPARATOR);
        $this->resolver
            ->setOption('path', array($dirBase.'/case_1/.php_cs.dist', $dirBase.'/case_1/foo.php'));
        $this->resolver->getConfig();
    }

    public function testResolveConfigFileChooseFileWithPathArrayAndConfig()
    {
        $dirBase = realpath(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'Fixtures'.DIRECTORY_SEPARATOR.'ConfigurationResolverConfigFile'.DIRECTORY_SEPARATOR);
        $this->resolver
            ->setOption('config', $dirBase.'/case_1/.php_cs.dist')
            ->setOption('path', array($dirBase.'/case_1/.php_cs.dist', $dirBase.'/case_1/foo.php'));
    }

    public function testResolvePathRelativeA()
    {
        $this->resolver
            ->setCwd(__DIR__)
            ->setOption('path', array('Command'));

        $this->assertSame(array(__DIR__.DIRECTORY_SEPARATOR.'Command'), $this->resolver->getPath());
    }

    public function testResolvePathRelativeB()
    {
        $this->resolver
            ->setCwd(dirname(__DIR__))
            ->setOption('path', array(basename(__DIR__)));

        $this->assertSame(array(__DIR__), $this->resolver->getPath());
    }

    public function testResolvePathWithFileThatIsExcludedDirectlyOverridePathMode()
    {
        $this->config->getFinder()
            ->in(__DIR__)
            ->notPath(basename(__FILE__));

        $this->resolver
            ->setOption('path', array(__FILE__));

        $this->assertCount(1, $this->resolver->getFinder());
    }

    public function testResolvePathWithFileThatIsExcludedDirectlyIntersectionPathMode()
    {
        $this->config->getFinder()
            ->in(__DIR__)
            ->notPath(basename(__FILE__));

        $this->resolver
            ->setOption('path', array(__FILE__))
            ->setOption('path-mode', 'intersection');

        $this->assertCount(0, $this->resolver->getFinder());
    }

    public function testResolvePathWithFileThatIsExcludedByDirOverridePathMode()
    {
        $dir = dirname(__DIR__);
        $this->config->getFinder()
            ->in($dir)
            ->exclude(basename(__DIR__));

        $this->resolver
            ->setOption('path', array(__FILE__));

        $this->assertCount(1, $this->resolver->getFinder());
    }

    public function testResolvePathWithFileThatIsExcludedByDirIntersectionPathMode()
    {
        $dir = dirname(__DIR__);
        $this->config->getFinder()
            ->in($dir)
            ->exclude(basename(__DIR__));

        $this->resolver
            ->setOption('path', array(__FILE__))
            ->setOption('path-mode', 'intersection');

        $this->assertCount(0, $this->resolver->getFinder());
    }

    public function testResolvePathWithFileThatIsNotExcluded()
    {
        $dir = __DIR__;
        $this->config->getFinder()
            ->in($dir)
            ->notPath('foo-'.basename(__FILE__));

        $this->resolver
            ->setOption('path', array(__FILE__));

        $this->assertCount(1, $this->resolver->getFinder());
    }

    /**
     * @dataProvider provideResolveIntersectionOfPathsCases
     */
    public function testResolveIntersectionOfPaths($expected, $configFinder, array $path, $pathMode, $config = null)
    {
        if ($expected instanceof \Exception) {
            $this->setExpectedException(get_class($expected));
        }

        if (null !== $configFinder) {
            $this->config->setFinder($configFinder);
        }

        $this->resolver
            ->setOption('path', $path)
            ->setOption('path-mode', $pathMode)
            ->setOption('config', $config)
        ;

        $intersectionItems = array_map(
            function (\SplFileInfo $file) {
                return $file->getRealPath();
            },
            iterator_to_array($this->resolver->getFinder(), false)
        );

        sort($expected);
        sort($intersectionItems);

        $this->assertSame($expected, $intersectionItems);
    }

    public function provideResolveIntersectionOfPathsCases()
    {
        $dir = __DIR__.'/../Fixtures/ConfigurationResolverPathsIntersection';
        $cb = function (array $items) use ($dir) {
            return array_map(
                function ($item) use ($dir) {
                    return realpath($dir.'/'.$item);
                },
                $items
            );
        };

        return array(
            'no path at all' => array(
                new \LogicException(),
                Finder::create(),
                array(),
                'override',
            ),
            'configured only by finder' => array(
                // don't override if the argument is empty
                $cb(array('a1.php', 'a2.php', 'b/b1.php', 'b/b2.php', 'b_b/b_b1.php', 'c/c1.php', 'c/d/cd1.php', 'd/d1.php', 'd/d2.php', 'd/e/de1.php', 'd/f/df1.php')),
                Finder::create()
                    ->in($dir),
                array(),
                'override',
            ),
            'configured only by argument' => array(
                $cb(array('a1.php', 'a2.php', 'b/b1.php', 'b/b2.php', 'b_b/b_b1.php', 'c/c1.php', 'c/d/cd1.php', 'd/d1.php', 'd/d2.php', 'd/e/de1.php', 'd/f/df1.php')),
                Finder::create(),
                array($dir),
                'override',
            ),
            'configured by finder, intersected with empty argument' => array(
                array(),
                Finder::create()
                    ->in($dir),
                array(),
                'intersection',
            ),
            'configured by finder, intersected with dir' => array(
                $cb(array('c/c1.php', 'c/d/cd1.php')),
                Finder::create()
                    ->in($dir),
                array($dir.'/c'),
                'intersection',
            ),
            'configured by finder, intersected with file' => array(
                $cb(array('c/c1.php')),
                Finder::create()
                    ->in($dir),
                array($dir.'/c/c1.php'),
                'intersection',
            ),
            'finder points to one dir while argument to another, not connected' => array(
                array(),
                Finder::create()
                    ->in($dir.'/b'),
                array($dir.'/c'),
                'intersection',
            ),
            'finder with excluded dir, intersected with excluded file' => array(
                array(),
                Finder::create()
                    ->in($dir)
                    ->exclude('c'),
                array($dir.'/c/d/cd1.php'),
                'intersection',
            ),
            'finder with excluded dir, intersected with dir containing excluded one' => array(
                $cb(array('c/c1.php')),
                Finder::create()
                    ->in($dir)
                    ->exclude('c/d'),
                array($dir.'/c'),
                'intersection',
            ),
            'finder with excluded file, intersected with dir containing excluded one' => array(
                $cb(array('c/d/cd1.php')),
                Finder::create()
                    ->in($dir)
                    ->notPath('c/c1.php'),
                array($dir.'/c'),
                'intersection',
            ),
            'configured by finder, intersected with non-existing path' => array(
                new \LogicException(),
                Finder::create()
                    ->in($dir),
                array('non_existing_dir'),
                'intersection',
            ),
            'configured by config file, overriden by multiple files' => array(
                $cb(array('d/d1.php', 'd/d2.php')),
                null,
                array($dir.'/d/d1.php', $dir.'/d/d2.php'),
                'override',
                $dir.'/d/.php_cs',
            ),
            'configured by config file, intersected with multiple files' => array(
                $cb(array('d/d1.php', 'd/d2.php')),
                null,
                array($dir.'/d/d1.php', $dir.'/d/d2.php'),
                'intersection',
                $dir.'/d/.php_cs',
            ),
            'configured by config file, overriden by non-existing dir' => array(
                new \LogicException(),
                null,
                array($dir.'/d/fff'),
                'override',
                $dir.'/d/.php_cs',
            ),
            'configured by config file, intersected with non-existing dir' => array(
                new \LogicException(),
                null,
                array($dir.'/d/fff'),
                'intersection',
                $dir.'/d/.php_cs',
            ),
            'configured by config file, overriden by non-existing file' => array(
                new \LogicException(),
                null,
                array($dir.'/d/fff.php'),
                'override',
                $dir.'/d/.php_cs',
            ),
            'configured by config file, intersected with non-existing file' => array(
                new \LogicException(),
                null,
                array($dir.'/d/fff.php'),
                'intersection',
                $dir.'/d/.php_cs',
            ),
            'configured by config file, overriden by multiple files and dirs' => array(
                $cb(array('d/d1.php', 'd/e/de1.php', 'd/f/df1.php')),
                null,
                array($dir.'/d/d1.php', $dir.'/d/e', $dir.'/d/f/'),
                'override',
                $dir.'/d/.php_cs',
            ),
            'configured by config file, intersected with multiple files and dirs' => array(
                $cb(array('d/d1.php', 'd/e/de1.php', 'd/f/df1.php')),
                null,
                array($dir.'/d/d1.php', $dir.'/d/e', $dir.'/d/f/'),
                'intersection',
                $dir.'/d/.php_cs',
            ),
        );
    }

    public function testResolveIsDryRunViaStdIn()
    {
        $this->resolver
            ->setOption('path', array('-'))
            ->setOption('dry-run', false);

        $this->assertTrue($this->resolver->isDryRun());
    }

    public function testResolveIsDryRunViaNegativeOption()
    {
        $this->resolver
            ->setOption('dry-run', false);

        $this->assertFalse($this->resolver->isDryRun());
    }

    public function testResolveIsDryRunViaPositiveOption()
    {
        $this->resolver
            ->setOption('dry-run', true);

        $this->assertTrue($this->resolver->isDryRun());
    }

    public function testResolveUsingCacheWithPositiveConfigAndPositiveOption()
    {
        $this->config->setUsingCache(true);
        $this->resolver
            ->setOption('using-cache', 'yes');

        $this->assertTrue($this->resolver->getUsingCache());
    }

    public function testResolveUsingCacheWithPositiveConfigAndNegativeOption()
    {
        $this->config->setUsingCache(true);
        $this->resolver
            ->setOption('using-cache', 'no');

        $this->assertFalse($this->resolver->getUsingCache());
    }

    public function testResolveUsingCacheWithNegativeConfigAndPositiveOption()
    {
        $this->config->setUsingCache(false);
        $this->resolver
            ->setOption('using-cache', 'yes');

        $this->assertTrue($this->resolver->getUsingCache());
    }

    public function testResolveUsingCacheWithNegativeConfigAndNegativeOption()
    {
        $this->config->setUsingCache(false);
        $this->resolver
            ->setOption('using-cache', 'no');

        $this->assertFalse($this->resolver->getUsingCache());
    }

    public function testResolveUsingCacheWithPositiveConfigAndNoOption()
    {
        $this->config->setUsingCache(true);
        $this->resolver;

        $this->assertTrue($this->resolver->getUsingCache());
    }

    public function testResolveUsingCacheWithNegativeConfigAndNoOption()
    {
        $this->config->setUsingCache(false);
        $this->resolver;

        $this->assertFalse($this->resolver->getUsingCache());
    }

    public function testResolveCacheFileWithoutConfigAndOption()
    {
        $default = $this->config->getCacheFile();

        $this->assertSame($default, $this->resolver->getCacheFile());
    }

    public function testResolveCacheFileWithConfig()
    {
        $cacheFile = 'foo/bar.baz';

        $this->config->setCacheFile($cacheFile);

        $this->assertSame($cacheFile, $this->resolver->getCacheFile());
    }

    public function testResolveCacheFileWithOption()
    {
        $cacheFile = 'bar.baz';

        $this->config->setCacheFile($cacheFile);
        $this->resolver->setOption('cache-file', $cacheFile);

        $this->assertSame($cacheFile, $this->resolver->getCacheFile());
    }

    public function testResolveCacheFileWithConfigAndOption()
    {
        $configCacheFile = 'foo/bar.baz';
        $optionCacheFile = 'bar.baz';

        $this->config->setCacheFile($configCacheFile);
        $this->resolver->setOption('cache-file', $optionCacheFile);

        $this->assertSame($optionCacheFile, $this->resolver->getCacheFile());
    }

    public function testResolveAllowRiskyWithPositiveConfigAndPositiveOption()
    {
        $this->config->setRiskyAllowed(true);
        $this->resolver
            ->setOption('allow-risky', 'yes');

        $this->assertTrue($this->resolver->getRiskyAllowed());
    }

    public function testResolveAllowRiskyWithPositiveConfigAndNegativeOption()
    {
        $this->config->setRiskyAllowed(true);
        $this->resolver
            ->setOption('allow-risky', 'no');

        $this->assertFalse($this->resolver->getRiskyAllowed());
    }

    public function testResolveAllowRiskyWithNegativeConfigAndPositiveOption()
    {
        $this->config->setRiskyAllowed(false);
        $this->resolver
            ->setOption('allow-risky', 'yes');

        $this->assertTrue($this->resolver->getRiskyAllowed());
    }

    public function testResolveAllowRiskyWithNegativeConfigAndNegativeOption()
    {
        $this->config->setRiskyAllowed(false);
        $this->resolver
            ->setOption('allow-risky', 'no');

        $this->assertFalse($this->resolver->getRiskyAllowed());
    }

    public function testResolveAllowRiskyWithPositiveConfigAndNoOption()
    {
        $this->config->setRiskyAllowed(true);

        $this->assertTrue($this->resolver->getRiskyAllowed());
    }

    public function testResolveAllowRiskyWithNegativeConfigAndNoOption()
    {
        $this->config->setRiskyAllowed(false);

        $this->assertFalse($this->resolver->getRiskyAllowed());
    }

    public function testResolveRulesWithConfig()
    {
        $this->config->setRules(array(
            'braces' => true,
            'strict_comparison' => false,
        ));

        $this->assertSameRules(
            array(
                'braces' => true,
            ),
            $this->resolver->getRules()
        );
    }

    public function testResolveRulesWithOption()
    {
        $this->resolver->setOption('rules', 'braces,-strict');

        $this->assertSameRules(
            array(
                'braces' => true,
            ),
            $this->resolver->getRules()
        );
    }

    public function testResolveRulesWithConfigAndOption()
    {
        $this->config->setRules(array(
            'braces' => true,
            'strict_comparison' => false,
        ));

        $this->resolver->setOption('rules', 'blank_line_before_return');

        $this->assertSameRules(
            array(
                'blank_line_before_return' => true,
            ),
            $this->resolver->getRules()
        );
    }

    protected function makeFixersTest($expectedFixers, $resolvedFixers)
    {
        $this->assertCount(count($expectedFixers), $resolvedFixers);

        foreach ($expectedFixers as $fixer) {
            $this->assertContains($fixer, $resolvedFixers);
        }
    }

    private function assertSameRules(array $expected, array $actual, $message = '')
    {
        ksort($expected);
        ksort($actual);

        $this->assertSame($expected, $actual, $message);
    }
}
