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

namespace PhpCsFixer\Error;

use PhpCsFixer\Linter\LintingException;

/**
 * @internal
 */
final class SourceErrorFromWorker extends \RuntimeException
{
    /**
     * @param array{class: string, message: string, code: int, file: string, line: int} $error
     */
    public static function fromArray(array $error): \RuntimeException
    {
        static $sourceExceptionFactory = [
            LintingException::class => static fn () => new LintingException($error['message'], $error['code']),
            '__default' => static fn () => new self(sprintf('[%s]: %s', $error['class'], $error['message']), $error['code']),
        ];

        $exception = ($sourceExceptionFactory[$error['class']] ?? $sourceExceptionFactory['__default'])();
        $exception->file = $error['file'];
        $exception->line = $error['line'];

        return $exception;
    }
}
