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
 * @author Filippo Tessarotto <zoeslam@gmail.com>
 *
 * @internal
 *
 * @covers \PhpCsFixer\Fixer\PhpUnit\PhpUnitMethodCasingFixer
 *
 * @extends AbstractFixerTestCase<\PhpCsFixer\Fixer\PhpUnit\PhpUnitMethodCasingFixer>
 *
 * @phpstan-import-type _AutogeneratedInputConfiguration from \PhpCsFixer\Fixer\PhpUnit\PhpUnitMethodCasingFixer
 */
final class PhpUnitMethodCasingFixerTest extends AbstractFixerTestCase
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
     * @return iterable<array{0: string, 1?: null|string, 2?: _AutogeneratedInputConfiguration}>
     */
    public static function provideFixCases(): iterable
    {
        yield 'skip non phpunit methods' => [
            '<?php class MyClass {
                    public function testMyApp() {}
                    public function test_my_app() {}
                }',
        ];

        yield 'skip non test methods' => [
            '<?php class MyTest extends \PhpUnit\FrameWork\TestCase {
                    public function not_a_test() {}
                    public function notATestEither() {}
                }',
        ];

        foreach (self::pairs() as $key => [$camelCase, $snakeCase]) {
            yield $key.' to camel case' => [$camelCase, $snakeCase];

            yield $key.' to snake case' => [$snakeCase, $camelCase, ['case' => 'snake_case']];
        }

        yield 'mixed case to camel case' => [
            '<?php class MyTest extends TestCase { function testShouldNotFooWhenBar() {} }',
            '<?php class MyTest extends TestCase { function test_should_notFoo_When_Bar() {} }',
        ];

        yield 'mixed case to snake case' => [
            '<?php class MyTest extends TestCase { function test_should_not_foo_when_bar() {} }',
            '<?php class MyTest extends TestCase { function test_should_notFoo_When_Bar() {} }',
            ['case' => 'snake_case'],
        ];
    }

    /**
     * @param _AutogeneratedInputConfiguration $configuration
     *
     * @dataProvider provideFix80Cases
     *
     * @requires PHP 8.0
     */
    public function testFix80(string $expected, ?string $input = null, array $configuration = []): void
    {
        $this->fixer->configure($configuration);
        $this->doTest($expected, $input);
    }

    /**
     * @return iterable<string, array{0: string, 1?: string}>
     */
    public static function provideFix80Cases(): iterable
    {
        yield '@depends annotation with class name in Snake_Case' => [
            '<?php class MyTest extends \PhpUnit\FrameWork\TestCase {
                public function testMyApp () {}

                /**
                 * @depends Foo_Bar_Test::testMyApp
                 */
                #[SimpleTest]
                public function testMyAppToo() {}
            }',
            '<?php class MyTest extends \PhpUnit\FrameWork\TestCase {
                public function test_my_app () {}

                /**
                 * @depends Foo_Bar_Test::test_my_app
                 */
                #[SimpleTest]
                public function test_my_app_too() {}
            }',
        ];

        yield '@depends annotation with class name in Snake_Case and attributes in between' => [
            '<?php class MyTest extends \PhpUnit\FrameWork\TestCase {
                public function testMyApp () {}

                /**
                 * @depends Foo_Bar_Test::testMyApp
                 */
                #[SimpleTest]
                #[Deprecated]
                public function testMyAppToo() {}
            }',
            '<?php class MyTest extends \PhpUnit\FrameWork\TestCase {
                public function test_my_app () {}

                /**
                 * @depends Foo_Bar_Test::test_my_app
                 */
                #[SimpleTest]
                #[Deprecated]
                public function test_my_app_too() {}
            }',
        ];

        yield 'test method with imported Test attribute' => [
            '<?php
            use PHPUnit\Framework\Attributes\Test;
            class MyTest extends \PHPUnit\Framework\TestCase {
                #[Test]
                public function myAppToo() {}
            }',
            '<?php
            use PHPUnit\Framework\Attributes\Test;
            class MyTest extends \PHPUnit\Framework\TestCase {
                #[Test]
                public function my_app_too() {}
            }',
        ];

        yield 'test method with fully qualified Test attribute' => [
            '<?php class MyTest extends \PHPUnit\Framework\TestCase {
            #[\PHPUnit\Framework\Attributes\Test]
            public function testMyApp() {}
            }',
            '<?php class MyTest extends \PHPUnit\Framework\TestCase {
            #[\PHPUnit\Framework\Attributes\Test]
            public function test_my_app() {}
            }',
        ];

        yield 'test method with multiple attributes' => [
            '<?php
            use PHPUnit\Framework\Attributes\Test;
            class MyTest extends \PHPUnit\Framework\TestCase {
            #[\PHPUnit\Framework\Attributes\After, Test]
            public function myAppToo() {}
            }',
            '<?php
            use PHPUnit\Framework\Attributes\Test;
            class MyTest extends \PHPUnit\Framework\TestCase {
            #[\PHPUnit\Framework\Attributes\After, Test]
            public function my_app_too() {}
            }',
        ];

        yield 'test method with multiple custom attributes' => [
            '<?php
            use PHPUnit\Framework\Attributes\Test;
            class MyTest extends \PHPUnit\Framework\TestCase {
            #[SimpleTest, Test]
            public function myAppToo() {}
            }',
            '<?php
            use PHPUnit\Framework\Attributes\Test;
            class MyTest extends \PHPUnit\Framework\TestCase {
            #[SimpleTest, Test]
            public function my_app_too() {}
            }',
        ];

        yield 'mixed test methods with and without attributes' => [
            '<?php
            use PHPUnit\Framework\Attributes\Test;
            class MyTest extends \PHPUnit\Framework\TestCase {
            public function testMyApp() {}
            #[Test]
            public function myAppToo() {}
            }',
            '<?php
            use PHPUnit\Framework\Attributes\Test;
            class MyTest extends \PHPUnit\Framework\TestCase {
            public function test_my_app() {}
            #[Test]
            public function my_app_too() {}
            }',
        ];

        yield 'snake_case conversion with attribute' => [
            '<?php
            use PHPUnit\Framework\Attributes\Test;
            class MyTest extends \PHPUnit\Framework\TestCase {
            #[Test]
            public function this_is_a_test_snake_case() {}
            }',
            '<?php
            use PHPUnit\Framework\Attributes\Test;
            class MyTest extends \PHPUnit\Framework\TestCase {
            #[Test]
            public function this_is_a_Test_Snake_Case() {}
            }',
            ['case' => 'snake_case'],
        ];

        yield 'camelCase to snake_case conversion with attribute' => [
            '<?php
                use \PHPUnit\Framework\Attributes\Test;
                class MyTest extends \PhpUnit\FrameWork\TestCase {
                #[Test]
                public function this_is_a_test_snake_case() {}
            }',
            '<?php
                use \PHPUnit\Framework\Attributes\Test;
                class MyTest extends \PhpUnit\FrameWork\TestCase {
                #[Test]
                public function this_is_a_TestSnakeCase() {}
            }',
            ['case' => 'snake_case'],
        ];

        yield 'method with attribute and docblock' => [
            '<?php
            use PHPUnit\Framework\Attributes\Test;
            class MyTest extends \PHPUnit\Framework\TestCase {
            #[Test]
            /** @return void */
            public function myAppToo() {}
            }',
            '<?php
            use PHPUnit\Framework\Attributes\Test;
            class MyTest extends \PHPUnit\Framework\TestCase {
            #[Test]
            /** @return void */
            public function my_app_too() {}
            }',
        ];

        yield 'method with attribute and docblock and multiple lines' => [
            '<?php
            use PHPUnit\Framework\Attributes\Test;
            class MyTest extends \PHPUnit\Framework\TestCase {
            #[Test]



            /** @return void */
            public function myAppToo() {}
            }',
            '<?php
            use PHPUnit\Framework\Attributes\Test;
            class MyTest extends \PHPUnit\Framework\TestCase {
            #[Test]



            /** @return void */
            public function my_app_too() {}
            }',
        ];

        yield 'method with multiple non-PHPUnit attributes' => [
            '<?php
            use PHPUnit\Framework\Attributes\Test;
            class MyTest extends \PHPUnit\Framework\TestCase {
                #[Author("John"), Test, Deprecated]
                public function myAppToo() {}
            }',
            '<?php
            use PHPUnit\Framework\Attributes\Test;
            class MyTest extends \PHPUnit\Framework\TestCase {
                #[Author("John"), Test, Deprecated]
                public function my_app_too() {}
            }',
        ];

        yield 'test attribute with fully qualified namespace without import' => [
            '<?php
            class MyTest extends \PHPUnit\Framework\TestCase {
                #[\PHPUnit\Framework\Attributes\Test]
                public function myAppToo() {}
            }',
            '<?php
            class MyTest extends \PHPUnit\Framework\TestCase {
                #[\PHPUnit\Framework\Attributes\Test]
                public function my_app_too() {}
            }',
        ];

        yield 'method with both attribute and @test annotation' => [
            '<?php
            use PHPUnit\Framework\Attributes\Test;
            class MyTest extends \PHPUnit\Framework\TestCase {
                #[Test]
                /** @test */
                public function myAppToo() {}
            }',
            '<?php
            use PHPUnit\Framework\Attributes\Test;
            class MyTest extends \PHPUnit\Framework\TestCase {
                #[Test]
                /** @test */
                public function my_app_too() {}
            }',
        ];

        yield 'method with parameters and attribute' => [
            '<?php
            use PHPUnit\Framework\Attributes\Test;
            class MyTest extends \PHPUnit\Framework\TestCase {
                #[Test]
                public function myAppToo(int $param): void {}
            }',
            '<?php
            use PHPUnit\Framework\Attributes\Test;
            class MyTest extends \PHPUnit\Framework\TestCase {
                #[Test]
                public function my_app_too(int $param): void {}
            }',
        ];

        yield 'test attribute in namespaced class' => [
            '<?php
            namespace App\Tests;

            use PHPUnit\Framework\Attributes\Test;

            class MyTest extends \PHPUnit\Framework\TestCase {
                #[Test]
                public function myAppToo() {}
            }',
            '<?php
            namespace App\Tests;

            use PHPUnit\Framework\Attributes\Test;

            class MyTest extends \PHPUnit\Framework\TestCase {
                #[Test]
                public function my_app_too() {}
            }',
        ];

        yield 'test attribute with alias' => [
            '<?php
            use PHPUnit\Framework\Attributes\Test as CheckTest;
            class MyTest extends \PHPUnit\Framework\TestCase {
                #[CheckTest]
                public function myAppToo() {}
            }',
            '<?php
            use PHPUnit\Framework\Attributes\Test as CheckTest;
            class MyTest extends \PHPUnit\Framework\TestCase {
                #[CheckTest]
                public function my_app_too() {}
            }',
        ];

        yield 'multiple attributes spanning multiple lines' => [
            '<?php
            use PHPUnit\Framework\Attributes\Test;
            class MyTest extends \PHPUnit\Framework\TestCase {
                #[
                    Test,
                    DataProvider("testData"),
                    Group("testData")
                ]
                public function myAppToo() {}
            }',
            '<?php
            use PHPUnit\Framework\Attributes\Test;
            class MyTest extends \PHPUnit\Framework\TestCase {
                #[
                    Test,
                    DataProvider("testData"),
                    Group("testData")
                ]
                public function my_app_too() {}
            }',
        ];

        yield 'multiple attributes in separate attribute blocks with comments' => [
            '<?php
            class MyTest extends \PHPUnit\Framework\TestCase {
                #[FirstAttribute]
                /* a comment */
                #[PHPUnit\Framework\Attributes\Test]
                // another comment
                #[LastAttribute]
                public function myAppToo() {}
            }',
            '<?php
            class MyTest extends \PHPUnit\Framework\TestCase {
                #[FirstAttribute]
                /* a comment */
                #[PHPUnit\Framework\Attributes\Test]
                // another comment
                #[LastAttribute]
                public function my_app_too() {}
            }',
        ];

        yield 'test attribute with trailing comma' => [
            '<?php
            use PHPUnit\Framework\Attributes\Test;
            class MyTest extends \PHPUnit\Framework\TestCase {
                #[Test, ]
                public function myAppToo() {}
            }',
            '<?php
            use PHPUnit\Framework\Attributes\Test;
            class MyTest extends \PHPUnit\Framework\TestCase {
                #[Test, ]
                public function my_app_too() {}
            }',
        ];

        yield 'method with both name prefix and attribute' => [
            '<?php
            use PHPUnit\Framework\Attributes\Test;
            class MyTest extends \PHPUnit\Framework\TestCase {
                #[Test]
                public function testMyAppToo() {}
            }',
            '<?php
            use PHPUnit\Framework\Attributes\Test;
            class MyTest extends \PHPUnit\Framework\TestCase {
                #[Test]
                public function test_my_app_too() {}
            }',
        ];

        yield 'attribute with different casing' => [
            '<?php
            use PHPUnit\Framework\Attributes\TEST;
            class MyTest extends \PHPUnit\Framework\TestCase {
                #[TEST]
                public function myAppToo() {}
            }',
            '<?php
            use PHPUnit\Framework\Attributes\TEST;
            class MyTest extends \PHPUnit\Framework\TestCase {
                #[TEST]
                public function my_app_too() {}
            }',
        ];

        yield 'do not touch anonymous class' => [
            <<<'PHP'
                <?php
                class MyTest extends \PHPUnit\Framework\TestCase {
                    #[PHPUnit\Framework\Attributes\Test]
                    public function methodFoo(): void
                    {
                        $class = new class () {
                            final public function method_bar(): void {}
                        };
                    }
                }
                PHP,
        ];
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    private static function pairs(): iterable
    {
        yield 'default sample' => [
            '<?php class MyTest extends \PhpUnit\FrameWork\TestCase { public function testMyApp() {} }',
            '<?php class MyTest extends \PhpUnit\FrameWork\TestCase { public function test_my_app() {} }',
        ];

        yield 'annotation' => [
            '<?php class MyTest extends \PhpUnit\FrameWork\TestCase { /** @test */ public function myApp() {} }',
            '<?php class MyTest extends \PhpUnit\FrameWork\TestCase { /** @test */ public function my_app() {} }',
        ];

        yield '@depends annotation' => [
            '<?php class MyTest extends \PhpUnit\FrameWork\TestCase {
                    public function testMyApp () {}

                    /**
                     * @depends testMyApp
                     */
                    public function testMyAppToo() {}
                }',
            '<?php class MyTest extends \PhpUnit\FrameWork\TestCase {
                    public function test_my_app () {}

                    /**
                     * @depends test_my_app
                     */
                    public function test_my_app_too() {}
                }',
        ];

        yield '@depends annotation with class name in PascalCase' => [
            '<?php class MyTest extends \PhpUnit\FrameWork\TestCase {
                    public function testMyApp () {}

                    /**
                     * @depends FooBarTest::testMyApp
                     */
                    public function testMyAppToo() {}
                }',
            '<?php class MyTest extends \PhpUnit\FrameWork\TestCase {
                    public function test_my_app () {}

                    /**
                     * @depends FooBarTest::test_my_app
                     */
                    public function test_my_app_too() {}
                }',
        ];

        yield '@depends annotation with class name in Snake_Case' => [
            '<?php class MyTest extends \PhpUnit\FrameWork\TestCase {
                    public function testMyApp () {}

                    /**
                     * @depends Foo_Bar_Test::testMyApp
                     */
                    public function testMyAppToo() {}
                }',
            '<?php class MyTest extends \PhpUnit\FrameWork\TestCase {
                    public function test_my_app () {}

                    /**
                     * @depends Foo_Bar_Test::test_my_app
                     */
                    public function test_my_app_too() {}
                }',
        ];

        yield '@depends and @test annotation' => [
            '<?php class MyTest extends \PhpUnit\FrameWork\TestCase {
                    /**
                     * @test
                     */
                    public function myApp () {}

                    /**
                     * @test
                     * @depends myApp
                     */
                    public function myAppToo() {}

                    /** not a test method */
                    public function my_app_not() {}

                    public function my_app_not_2() {}
                }',
            '<?php class MyTest extends \PhpUnit\FrameWork\TestCase {
                    /**
                     * @test
                     */
                    public function my_app () {}

                    /**
                     * @test
                     * @depends my_app
                     */
                    public function my_app_too() {}

                    /** not a test method */
                    public function my_app_not() {}

                    public function my_app_not_2() {}
                }',
        ];
    }
}
