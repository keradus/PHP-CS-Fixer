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

namespace PhpCsFixer\Tests\Fixer\NamespaceNotation;

use PhpCsFixer\ConfigurationException\InvalidFixerConfigurationException;
use PhpCsFixer\Tests\Test\AbstractFixerTestCase;
use PhpCsFixer\WhitespacesFixerConfig;

/**
 * @author Greg Korba <greg@codito.dev>
 *
 * @internal
 *
 * @covers \PhpCsFixer\Fixer\NamespaceNotation\BlankLinesBeforeNamespaceFixer
 *
 * @extends AbstractFixerTestCase<\PhpCsFixer\Fixer\NamespaceNotation\BlankLinesBeforeNamespaceFixer>
 *
 * @phpstan-import-type _AutogeneratedInputConfiguration from \PhpCsFixer\Fixer\NamespaceNotation\BlankLinesBeforeNamespaceFixer
 */
final class BlankLinesBeforeNamespaceFixerTest extends AbstractFixerTestCase
{
    /**
     * @param _AutogeneratedInputConfiguration $config
     *
     * @dataProvider provideFixCases
     */
    public function testFix(
        string $expected,
        ?string $input = null,
        ?array $config = [],
        ?WhitespacesFixerConfig $whitespaces = null
    ): void {
        if (null !== $whitespaces) {
            $this->fixer->setWhitespacesConfig($whitespaces);
        }
        $this->fixer->configure($config);
        $this->doTest($expected, $input);
    }

    /**
     * @return iterable<string, array{0: string, 1?: null|string, 2?: _AutogeneratedInputConfiguration}>
     */
    public static function provideFixCases(): iterable
    {
        yield 'multiple blank lines between namespace declaration and PHP opening tag' => [
            "<?php\n\n\n\nnamespace X;",
            "<?php\nnamespace X;",
            ['min_line_breaks' => 4, 'max_line_breaks' => 4],
        ];

        yield 'multiple blank lines between namespace declaration and comment' => [
            "<?php\n/* Foo */\n\n\nnamespace X;",
            "<?php\n/* Foo */\nnamespace X;",
            ['min_line_breaks' => 3, 'max_line_breaks' => 3],
        ];

        yield 'multiple blank lines within min and max line breaks range' => [
            "<?php\n\n\n\nnamespace X;",
            null,
            ['min_line_breaks' => 3, 'max_line_breaks' => 5],
        ];

        yield 'multiple blank lines with fewer line breaks than minimum' => [
            "<?php\n\n\nnamespace X;",
            "<?php\n\nnamespace X;",
            ['min_line_breaks' => 3, 'max_line_breaks' => 5],
        ];

        yield 'multiple blank lines with more line breaks than maximum' => [
            "<?php\n\n\nnamespace X;",
            "<?php\n\n\n\n\nnamespace X;",
            ['min_line_breaks' => 1, 'max_line_breaks' => 3],
        ];

        yield 'enforce namespace at the same line as opening tag' => [
            '<?php namespace X;',
            "<?php\n\n\n\n\nnamespace X;",
            ['min_line_breaks' => 0, 'max_line_breaks' => 0],
        ];
    }

    /**
     * @param _AutogeneratedInputConfiguration $configuration
     *
     * @dataProvider provideInvalidConfigurationCases
     */
    public function testInvalidConfiguration(array $configuration): void
    {
        $this->expectException(InvalidFixerConfigurationException::class);
        $this->fixer->configure($configuration);
    }

    /**
     * @return iterable<string, array{mixed}>
     */
    public static function provideInvalidConfigurationCases(): iterable
    {
        yield 'min not integer' => [['min_line_breaks' => true, 'max_line_breaks' => 2]];

        yield 'max not integer' => [['min_line_breaks' => 1, 'max_line_breaks' => 'two and a half']];

        yield 'min higher than max' => [['min_line_breaks' => 4, 'max_line_breaks' => 2]];

        yield 'min lower than 0' => [['min_line_breaks' => -2, 'max_line_breaks' => 2]];

        yield 'max lower than 0' => [['min_line_breaks' => -4, 'max_line_breaks' => -2]];

        yield 'extra option' => [['min_line_breaks' => 1, 'max_line_breaks' => 3, 'average_line_breaks' => 2]];
    }
}
