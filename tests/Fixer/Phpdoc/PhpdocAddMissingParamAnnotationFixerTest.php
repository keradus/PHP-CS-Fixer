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

namespace PhpCsFixer\Tests\Fixer\Phpdoc;

use PhpCsFixer\Test\AbstractFixerTestCase;

/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 */
final class PhpdocAddMissingParamAnnotationFixerTest extends AbstractFixerTestCase
{
    /**
     * @dataProvider provideFixCases
     */
    public function testFix($expected, $input = null, array $config = null)
    {
        $this->fixer->configure($config ? $config : ['only_untyped' => false]);

        $this->doTest($expected, $input);
    }

    public function provideFixCases()
    {
        return array(
            array(
                '<?php
    /**
     *
     */',
            ),
            array(
                '<?php
    /**
     * @param int $foo
     * @param mixed $bar
     */
    function f1($foo, $bar) {}',
                '<?php
    /**
     * @param int $foo
     */
    function f1($foo, $bar) {}',
            ),
            array(
                '<?php
    /**
     * @param int $bar
     * @param mixed $foo
     */
    function f2($foo, $bar) {}',
                '<?php
    /**
     * @param int $bar
     */
    function f2($foo, $bar) {}',
            ),
            array(
                '<?php
    /**
     * @return void
     * @param mixed $foo
     * @param mixed $bar
     */
    function f3($foo, $bar) {}',
                '<?php
    /**
     * @return void
     */
    function f3($foo, $bar) {}',
            ),
            array(
                '<?php
    class Foo {
        /**
         * @param int $bar
         * @param mixed $foo
         */
        public function f4a($foo, $bar) {}
    }',
                '<?php
    class Foo {
        /**
         * @param int $bar
         */
        public function f4a($foo, $bar) {}
    }',
            ),
            array(
                '<?php
    class Foo {
        /**
         * @param int $bar
         * @param mixed $foo
         */
        static public function f4b($foo, $bar) {}
    }',
                '<?php
    class Foo {
        /**
         * @param int $bar
         */
        static public function f4b($foo, $bar) {}
    }',
            ),
            array(
                '<?php
    class Foo {
        /**
         * @var int
         */
        private $foo;
    }',
            ),
            array(
                '<?php
    /**
     * @param $bar No type !!
     * @param mixed $foo
     */
    function f5($foo, $bar) {}',
                '<?php
    /**
     * @param $bar No type !!
     */
    function f5($foo, $bar) {}',
            ),
            array(
                '<?php
    /**
     * @param int
     * @param int $bar
     * @param Foo\Bar $foo
     */
    function f6(Foo\Bar $foo, $bar) {}',
                '<?php
    /**
     * @param int
     * @param int $bar
     */
    function f6(Foo\Bar $foo, $bar) {}',
            ),
            array(
                '<?php
    /**
     * @param int $bar
     * @param null|string $foo
     */
    function f7(string $foo = nuLl, $bar) {}',
                '<?php
    /**
     * @param int $bar
     */
    function f7(string $foo = nuLl, $bar) {}',
            ),
            array(
                '<?php
    /**
     * @param int $bar
     * @param string $foo
     */
    function f8(string $foo = "null", $bar) {}',
                '<?php
    /**
     * @param int $bar
     */
    function f8(string $foo = "null", $bar) {}',
            ),
            array(
                '<?php
    /**
     * @param int $bar
     * @param mixed $baz
     */
    function f9(string $foo, $bar, $baz) {}',
                '<?php
    /**
     * @param int $bar
     */
    function f9(string $foo, $bar, $baz) {}',
                ['only_untyped' => true],
            ),
            array(
                '<?php
    /**
     * @{inheritdoc}
     */
    function f10(string $foo = "null", $bar) {}',
            ),
            array(
                '<?php
    /**
     * @param bool|bool[] $caseSensitive Line 1
     *                                   Line 2
     */
     function f11($caseSensitive) {}
',
            )
        );
    }

    /**
     * @dataProvider provideCases71
     * @requires PHP 7.1
     */
    public function testFix71($expected, $input = null, array $config = null)
    {
        $this->fixer->configure($config ? $config : ['only_untyped' => false]);

        $this->doTest($expected, $input);
    }

    public function provideCases71()
    {
        return array(
            array(
                '<?php
    /**
     * @param int $bar
     * @param ?array $foo
     */
    function w1(?array $foo = null, $bar) {}',
                '<?php
    /**
     * @param int $bar
     */
    function w1(?array $foo = null, $bar) {}',
            ),
        );
    }
}
