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

namespace PhpCsFixer\Tests\Test;

use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\Linter\LinterInterface;
use PhpCsFixer\Tests\Test\Assert\AssertTokensTrait;
use PhpCsFixer\Tests\TestingBase\AbstractFixerTestCase as BaseAbstractFixerTestCase;
use PhpCsFixer\Tokenizer\Tokens;
use Prophecy\Argument;

/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 */
abstract class AbstractFixerTestCase extends BaseAbstractFixerTestCase
{
    use AssertTokensTrait;

    /**
     * @var LinterInterface
     */
    protected $linter;

    protected function setUp()
    {
        parent::setUp();

        // @todo remove at 3.0 together with env var itself
        if (getenv('PHP_CS_FIXER_TEST_USE_LEGACY_TOKENIZER')) {
            Tokens::setLegacyMode(true);
        }
    }

    protected function tearDown()
    {
        parent::tearDown();

        // @todo remove at 3.0
        Tokens::setLegacyMode(false);
    }

    /**
     * @return FixerInterface
     */
    protected function createFixer()
    {
        $fixerClassName = preg_replace('/^(PhpCsFixer)\\\\Tests(\\\\.+)Test$/', '$1$2', static::class);

        return new $fixerClassName();
    }

    /**
     * @return LinterInterface
     */
    protected function createLinter()
    {
        static $linter = null;

        if (null === $linter) {
            if (getenv('SKIP_LINT_TEST_CASES')) {
                $linterProphecy = $this->prophesize(\PhpCsFixer\Linter\LinterInterface::class);
                $linterProphecy
                    ->lintSource(Argument::type('string'))
                    ->willReturn($this->prophesize(\PhpCsFixer\Linter\LintingResultInterface::class)->reveal());

                $linter = $linterProphecy->reveal();
            } else {
                $linter = parent::createLinter();
            }
        }

        return $linter;
    }
}
