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

namespace PhpCsFixer\Tests\Fixer\PhpUnit;

use PhpCsFixer\Tests\Test\AbstractFixerTestCase;

/**
 * @internal
 *
 * @covers \PhpCsFixer\Fixer\PhpUnit\PhpUnitDataProviderMethodOrderFixer
 *
 * @extends AbstractFixerTestCase<\PhpCsFixer\Fixer\PhpUnit\PhpUnitDataProviderMethodOrderFixer>
 *
 * @phpstan-import-type _AutogeneratedInputConfiguration from \PhpCsFixer\Fixer\PhpUnit\PhpUnitDataProviderMethodOrderFixer
 */
final class PhpUnitDataProviderMethodOrderFixerTest extends AbstractFixerTestCase
{
    /**
     * @param _AutogeneratedInputConfiguration $configuration
     *
     * @dataProvider provideFixCases
     */
    public function testFix(string $expected, ?string $input = null, array $configuration = []): void
    {
        $this->fixer->configure($configuration);
        $this->doTest($expected, $input);
    }

    /**
     * @return iterable<string, array{0: string, 1?: null|string, 2?: array<string, string>}>
     */
    public static function provideFixCases(): iterable
    {
        yield 'simple - placement after' => [
            <<<'PHP'
                <?php
                class FooTest extends TestCase {
                    /** @dataProvider provideFooCases */
                    public function testFoo(): void {}
                    public function provideFooCases(): iterable {}
                }
                PHP,
            <<<'PHP'
                <?php
                class FooTest extends TestCase {
                    public function provideFooCases(): iterable {}
                    /** @dataProvider provideFooCases */
                    public function testFoo(): void {}
                }
                PHP,
        ];

        yield 'simple - placement before' => [
            <<<'PHP'
                <?php
                class FooTest extends TestCase {
                    public function provideFooCases(): iterable {}
                    /** @dataProvider provideFooCases */
                    public function testFoo(): void {}
                }
                PHP,
            <<<'PHP'
                <?php
                class FooTest extends TestCase {
                    /** @dataProvider provideFooCases */
                    public function testFoo(): void {}
                    public function provideFooCases(): iterable {}
                }
                PHP,
            ['placement' => 'before'],
        ];

        yield 'empty test class' => [
            <<<'PHP'
                <?php
                class FooTest extends TestCase {}
                PHP,
        ];

        yield 'data provider named with different casing' => [
            <<<'PHP'
                <?php
                class FooTest extends TestCase {
                    /** @dataProvider provideFooCases */
                    public function testFoo(): void {}
                    public function PROVIDEFOOCASES(): iterable {}
                }
                PHP,
            <<<'PHP'
                <?php
                class FooTest extends TestCase {
                    public function PROVIDEFOOCASES(): iterable {}
                    /** @dataProvider provideFooCases */
                    public function testFoo(): void {}
                }
                PHP,
        ];

        yield 'with test method annotated' => [
            <<<'PHP'
                <?php
                class FooTest extends TestCase {
                    /**
                     * @test
                     * @dataProvider provideFooCases
                     */
                    public function foo(): void {}
                    public function provideFooCases(): iterable {}
                }
                PHP,
            <<<'PHP'
                <?php
                class FooTest extends TestCase {
                    public function provideFooCases(): iterable {}
                    /**
                     * @test
                     * @dataProvider provideFooCases
                     */
                    public function foo(): void {}
                }
                PHP,
        ];

        yield 'data provider not found' => [
            <<<'PHP'
                <?php
                class FooTest extends TestCase {
                    public function provideFooCases(): iterable {}
                    /** @dataProvider notExistingFunction */
                    public function testFoo(): void {}
                }
                PHP,
        ];

        yield 'data provider used multiple times - placement after' => [
            <<<'PHP'
                <?php
                class FooTest extends TestCase {
                    /** @dataProvider provideFooCases */
                    public function testFoo(): void {}
                    /** @dataProvider provideFooCases */
                    public function testFoo2(): void {}
                    public function provideFooCases(): iterable {}
                }
                PHP,
            <<<'PHP'
                <?php
                class FooTest extends TestCase {
                    public function provideFooCases(): iterable {}
                    /** @dataProvider provideFooCases */
                    public function testFoo(): void {}
                    /** @dataProvider provideFooCases */
                    public function testFoo2(): void {}
                }
                PHP,
        ];

        yield 'data provider used multiple times - placement after - do not move provider if already after first test where used' => [
            <<<'PHP'
                <?php
                class FooTest extends TestCase {
                    /** @dataProvider provideFooCases */
                    public function testFoo(): void {}
                    public function provideFooCases(): iterable {}
                    /** @dataProvider provideFooCases */
                    public function testBar(): void {}
                }
                PHP,
        ];

        yield 'data provider used multiple times - placement before' => [
            <<<'PHP'
                <?php
                class FooTest extends TestCase {
                    public function provideFooCases(): iterable {}
                    /** @dataProvider provideFooCases */
                    public function testFoo(): void {}
                    /** @dataProvider provideFooCases */
                    public function testFoo2(): void {}
                }
                PHP,
            <<<'PHP'
                <?php
                class FooTest extends TestCase {
                    /** @dataProvider provideFooCases */
                    public function testFoo(): void {}
                    /** @dataProvider provideFooCases */
                    public function testFoo2(): void {}
                    public function provideFooCases(): iterable {}
                }
                PHP,
            ['placement' => 'before'],
        ];

        yield 'multiple data providers for one test method' => [
            <<<'PHP'
                <?php
                class FooTest extends TestCase {
                    /**
                     * @dataProvider provideFooCases
                     * @dataProvider provideFooCases3
                     * @dataProvider provideFooCases2
                     */
                    public function testFoo(): void {}
                    public function provideFooCases(): iterable {}
                    public function provideFooCases3(): iterable {}
                    public function provideFooCases2(): iterable {}
                }
                PHP,
            <<<'PHP'
                <?php
                class FooTest extends TestCase {
                    public function provideFooCases(): iterable {}
                    /**
                     * @dataProvider provideFooCases
                     * @dataProvider provideFooCases3
                     * @dataProvider provideFooCases2
                     */
                    public function testFoo(): void {}
                    public function provideFooCases2(): iterable {}
                    public function provideFooCases3(): iterable {}
                }
                PHP,
        ];

        yield 'data provider used multiple times II - placement after' => [
            <<<'PHP'
                <?php
                class FooTest {
                    /** @dataProvider provideACases */
                    public function testA1(): void {}

                    /** @dataProvider provideBCases */
                    public function testB(): void {}

                    public static function provideBCases(): iterable {}

                    /** @dataProvider provideACases */
                    public function testA2(): void {}

                    public static function provideACases(): iterable {}
                }
                PHP,
        ];

        yield 'data provider used multiple times II - placement before' => [
            <<<'PHP'
                <?php
                class FooTest {
                    public static function provideACases(): iterable {}

                    /** @dataProvider provideACases */
                    public function testA2(): void {}

                    public static function provideBCases(): iterable {}

                    /** @dataProvider provideBCases */
                    public function testB(): void {}

                    /** @dataProvider provideACases */
                    public function testA1(): void {}
                }
                PHP,
            null,
            ['placement' => 'before'],
        ];

        yield 'with other methods - placement after' => [
            <<<'PHP'
                <?php
                class FooTest extends TestCase {
                    public function testA(): void {}
                    /** @dataProvider provideFooCases */
                    public function testFoo(): void {}
                    public function provideFooCases(): iterable {}
                    public function testB(): void {}
                }
                PHP,
            <<<'PHP'
                <?php
                class FooTest extends TestCase {
                    public function testA(): void {}
                    public function provideFooCases(): iterable {}
                    /** @dataProvider provideFooCases */
                    public function testFoo(): void {}
                    public function testB(): void {}
                }
                PHP,
        ];

        yield 'with other methods - placement before' => [
            <<<'PHP'
                <?php
                class FooTest extends TestCase {
                    public function testA(): void {}
                    public function provideFooCases(): iterable {}
                    /** @dataProvider provideFooCases */
                    public function testFoo(): void {}
                    public function testB(): void {}
                }
                PHP,
            <<<'PHP'
                <?php
                class FooTest extends TestCase {
                    public function testA(): void {}
                    /** @dataProvider provideFooCases */
                    public function testFoo(): void {}
                    public function provideFooCases(): iterable {}
                    public function testB(): void {}
                }
                PHP,
            ['placement' => 'before'],
        ];

        yield 'with other methods II' => [
            <<<'PHP'
                <?php
                class FooTest extends TestCase {
                    public function testA(): void {}
                    public function testB(): void {}
                    /** @dataProvider provideFooCases */
                    public function testFoo(): void {}
                    public function provideFooCases(): iterable {}
                    public function testC(): void {}
                }
                PHP,
            <<<'PHP'
                <?php
                class FooTest extends TestCase {
                    public function testA(): void {}
                    public function provideFooCases(): iterable {}
                    public function testB(): void {}
                    /** @dataProvider provideFooCases */
                    public function testFoo(): void {}
                    public function testC(): void {}
                }
                PHP,
        ];
    }

    /**
     * @requires PHP ^8.0
     *
     * @param _AutogeneratedInputConfiguration $configuration
     *
     * @dataProvider provideFix80Cases
     */
    public function testFix80(string $expected, ?string $input = null, array $configuration = []): void
    {
        $this->fixer->configure($configuration);
        $this->doTest($expected, $input);
    }

    /**
     * @return iterable<string, array{0: string, 1?: string, 2?: array<string, string>}>
     */
    public static function provideFix80Cases(): iterable
    {
        yield 'with an attribute between PHPDoc and data provider/test method - placement after' => [
            <<<'PHP'
                <?php
                class FooTest extends TestCase {
                    /**
                     * @dataProvider provideFooCases
                     */
                    #[CustomTestAttribute]
                    public function testFoo(): void {}
                    #[CustomProviderAttribute]
                    public function provideFooCases(): iterable {}
                }
                PHP,
            <<<'PHP'
                <?php
                class FooTest extends TestCase {
                    #[CustomProviderAttribute]
                    public function provideFooCases(): iterable {}
                    /**
                     * @dataProvider provideFooCases
                     */
                    #[CustomTestAttribute]
                    public function testFoo(): void {}
                }
                PHP,
        ];

        yield 'with an attribute between PHPDoc and data provider/test method - placement before' => [
            <<<'PHP'
                <?php
                class FooTest extends TestCase {
                    #[CustomProviderAttribute]
                    public function provideFooCases(): iterable {}
                    /**
                     * @dataProvider provideFooCases
                     */
                    #[CustomTestAttribute]
                    public function testFoo(): void {}
                }
                PHP,
            <<<'PHP'
                <?php
                class FooTest extends TestCase {
                    /**
                     * @dataProvider provideFooCases
                     */
                    #[CustomTestAttribute]
                    public function testFoo(): void {}
                    #[CustomProviderAttribute]
                    public function provideFooCases(): iterable {}
                }
                PHP,
            ['placement' => 'before'],
        ];

        yield 'data provider defined by an attribute' => [ // update expected once https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/pull/8197 is merged
            <<<'PHP'
                <?php
                class FooTest extends TestCase {
                    public function provideFooCases(): iterable {}
                    #[DataProvider('provideFooCases')]
                    public function testFoo(): void {}
                }
                PHP,
        ];
    }
}
