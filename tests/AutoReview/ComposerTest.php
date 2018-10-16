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

namespace PhpCsFixer\tests\AutoReview;

use PhpCsFixer\Console\Application;
use PhpCsFixer\Tests\TestCase;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 *
 * @coversNothing
 * @group auto-review
 * @group covers-nothing
 */
final class ComposerTest extends TestCase
{
    private static $rootComposerJson;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$rootComposerJson = json_decode(file_get_contents(__DIR__.'/../../composer.json'), true);
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

        self::$rootComposerJson = null;
    }

    public function testBranchAlias()
    {
        $composerJson = self::$rootComposerJson;

        if (!isset(self::$rootComposerJson['extra']['branch-alias'])) {
            $this->addToAssertionCount(1); // composer.json doesn't contain branch alias, all good!
            return;
        }

        $appVersion = $this->convertAppVersionToComposerVersion(Application::VERSION);
        $branchName = $appVersion;

        $this->assertSame(
            ['dev-'.$branchName => $appVersion.'-dev'],
            self::$rootComposerJson['extra']['branch-alias']
        );
    }

    /**
     * @dataProvider provideExtraComposerJsons
     *
     * @param mixed $json
     */
    public function testConsistencyOfAliases($json)
    {
        $this->assertSame(self::$rootComposerJson['extra']['branch-alias'], $json['extra']['branch-alias']);
    }

    public function provideExtraComposerJsons()
    {
        $finder = Finder::create()
            ->files()
            ->name('composer.json')
            ->in(__DIR__.'/../..')
            ->notPath('/^composer.json/')
            ->exclude(['vendor', 'dev-tools'])
        ;

        $files = iterator_to_array($finder, false);
        $x = array_map(
            static function (SplFileInfo $file) {
                return [json_decode(file_get_contents($file), true)];
            },
            $files
        );

        return $x;
    }

    /**
     * @param string $version
     *
     * @return string
     */
    private function convertAppVersionToComposerVersion($version)
    {
        $parts = explode('.', $version, 3);

        return sprintf('%d.%d', $parts[0], $parts[1]);
    }
}
