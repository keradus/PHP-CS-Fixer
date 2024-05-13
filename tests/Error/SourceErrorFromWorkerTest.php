<?php

declare(strict_types=1);

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace PhpCsFixer\Tests\Error;

use PhpCsFixer\Error\SourceErrorFromWorker;
use PhpCsFixer\Linter\LintingException;
use PhpCsFixer\Tests\TestCase;

/**
 * @internal
 *
 * @covers \PhpCsFixer\Error\SourceErrorFromWorker
 */
final class SourceErrorFromWorkerTest extends TestCase
{
    public function testFromArrayForLintingException(): void
    {
        $exception = SourceErrorFromWorker::fromArray([
            'class' => LintingException::class,
            'message' => 'foo',
            'code' => 1,
            'file' => 'foo.php',
            'line' => 1,
        ]);

        self::assertInstanceOf(LintingException::class, $exception);
        self::assertSame('foo', $exception->getMessage());
        self::assertSame(1, $exception->getCode());
        self::assertSame('foo.php', $exception->getFile());
        self::assertSame(1, $exception->getLine());
    }

    public function testFromArrayForNonLintingException(): void
    {
        $exception = SourceErrorFromWorker::fromArray([
            'class' => \RangeException::class,
            'message' => 'foo',
            'code' => 1,
            'file' => 'foo.php',
            'line' => 1,
        ]);

        self::assertInstanceOf(SourceErrorFromWorker::class, $exception);
        self::assertSame('[RangeException]: foo', $exception->getMessage());
        self::assertSame(1, $exception->getCode());
        self::assertSame('foo.php', $exception->getFile());
        self::assertSame(1, $exception->getLine());
    }
}
