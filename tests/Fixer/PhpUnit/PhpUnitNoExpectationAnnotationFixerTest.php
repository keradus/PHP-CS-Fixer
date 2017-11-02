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

namespace PhpCsFixer\Tests\Fixer\PhpUnit;

use PhpCsFixer\Fixer\PhpUnit\PhpUnitTargetVersion;
use PhpCsFixer\Tests\Test\AbstractFixerTestCase;
use PhpCsFixer\WhitespacesFixerConfig;

/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 *
 * @covers \PhpCsFixer\Fixer\PhpUnit\PhpUnitNoExpectationAnnotationFixer
 */
final class PhpUnitNoExpectationAnnotationFixerTest extends AbstractFixerTestCase
{
    /**
     * @param string      $expected
     * @param null|string $input
     * @param array       $config
     *
     * @dataProvider provideTestFixCases
     */
    public function testFix($expected, $input = null, array $config = [])
    {
        $this->fixer->configure($config);
        $this->doTest($expected, $input);
    }

    public function provideTestFixCases()
    {
        return [
            'expecting exception' => [
                '<?php
    final class MyTest extends \PHPUnit_Framework_TestCase
    {
        /**
         */
        public function testFnc()
        {
            $this->setExpectedException(\FooException::class);
            aaa();
        }
    }',
                '<?php
    final class MyTest extends \PHPUnit_Framework_TestCase
    {
        /**
         * @expectedException FooException
         */
        public function testFnc()
        {
            aaa();
        }
    }',
            ],
            'expecting rooted exception' => [
                '<?php
    final class MyTest extends \PHPUnit_Framework_TestCase
    {
        /**
         */
        public function testFnc()
        {
            $this->setExpectedException(\FooException::class);
            aaa();
        }
    }',
                '<?php
    final class MyTest extends \PHPUnit_Framework_TestCase
    {
        /**
         * @expectedException \FooException
         */
        public function testFnc()
        {
            aaa();
        }
    }',
            ],
            'expecting exception with msg' => [
                '<?php
    final class MyTest extends \PHPUnit_Framework_TestCase
    {
        /**
         */
        public function testFnc()
        {
            $this->setExpectedException(\FooException::class, \'foo@bar\');
            aaa();
        }
    }',
                '<?php
    final class MyTest extends \PHPUnit_Framework_TestCase
    {
        /**
         * @expectedException FooException
         * @expectedExceptionMessage foo@bar
         */
        public function testFnc()
        {
            aaa();
        }
    }',
            ],
            'expecting exception with code' => [
                '<?php
    final class MyTest extends \PHPUnit_Framework_TestCase
    {
        /**
         */
        public function testFnc()
        {
            $this->setExpectedException(\FooException::class, null, 123);
            aaa();
        }
    }',
                '<?php
    final class MyTest extends \PHPUnit_Framework_TestCase
    {
        /**
         * @expectedException FooException
         * @expectedExceptionCode 123
         */
        public function testFnc()
        {
            aaa();
        }
    }',
            ],
            'expecting exception with msg and code' => [
                '<?php
    final class MyTest extends \PHPUnit_Framework_TestCase
    {
        /**
         */
        public function testFnc()
        {
            $this->setExpectedException(\FooException::class, \'foo\', 123);
            aaa();
        }
    }',
                '<?php
    final class MyTest extends \PHPUnit_Framework_TestCase
    {
        /**
         * @expectedException FooException
         * @expectedExceptionMessage foo
         * @expectedExceptionCode 123
         */
        public function testFnc()
        {
            aaa();
        }
    }',
            ],
            'expecting exception with msg regex [but too low target]' => [
                '<?php
    final class MyTest extends \PHPUnit_Framework_TestCase
    {
        /**
         * @expectedException FooException
         * @expectedExceptionMessageRegExp /foo.*$/
         */
        public function testFnc()
        {
            aaa();
        }
    }',
                null,
                ['target' => PhpUnitTargetVersion::VERSION_3_2],
            ],
            'expecting exception with msg regex' => [
                '<?php
    final class MyTest extends \PHPUnit_Framework_TestCase
    {
        /**
         */
        public function testFnc()
        {
            $this->setExpectedExceptionRegExp(\FooException::class, \'/foo.*$/\');
            aaa();
        }
    }',
                '<?php
    final class MyTest extends \PHPUnit_Framework_TestCase
    {
        /**
         * @expectedException FooException
         * @expectedExceptionMessageRegExp /foo.*$/
         */
        public function testFnc()
        {
            aaa();
        }
    }',
                ['target' => PhpUnitTargetVersion::VERSION_4_3],
            ],
            'use_class_const=false' => [
                '<?php
    final class MyTest extends \PHPUnit_Framework_TestCase
    {
        /**
         */
        public function testFnc()
        {
            $this->setExpectedException(\'\FooException\');
            aaa();
        }
    }',
                '<?php
    final class MyTest extends \PHPUnit_Framework_TestCase
    {
        /**
         * @expectedException FooException
         */
        public function testFnc()
        {
            aaa();
        }
    }',
                ['use_class_const' => false],
            ],
            'keep rest of docblock' => [
                '<?php
    final class MyTest extends \PHPUnit_Framework_TestCase
    {
        /**
         * Summary.
         *
         * @param int $param
         * @return void
         */
        public function testFnc($param)
        {
            $this->setExpectedException(\FooException::class);
            aaa();
        }
    }',
                '<?php
    final class MyTest extends \PHPUnit_Framework_TestCase
    {
        /**
         * Summary.
         *
         * @param int $param
         * @expectedException FooException
         * @return void
         */
        public function testFnc($param)
        {
            aaa();
        }
    }',
            ],
            'fix method without visibility' => [
                '<?php
    final class MyTest extends \PHPUnit_Framework_TestCase
    {
        /**
         */
        function testFnc($param)
        {
            $this->setExpectedException(\FooException::class);
            aaa();
        }
    }',
                '<?php
    final class MyTest extends \PHPUnit_Framework_TestCase
    {
        /**
         * @expectedException FooException
         */
        function testFnc($param)
        {
            aaa();
        }
    }',
            ],
            'fix final method' => [
                '<?php
    final class MyTest extends \PHPUnit_Framework_TestCase
    {
        /**
         */
        final function testFnc($param)
        {
            $this->setExpectedException(\FooException::class);
            aaa();
        }
    }',
                '<?php
    final class MyTest extends \PHPUnit_Framework_TestCase
    {
        /**
         * @expectedException FooException
         */
        final function testFnc($param)
        {
            aaa();
        }
    }',
            ],
            'ignore when no docblock' => [
                '<?php
    final class MyTest extends \PHPUnit_Framework_TestCase
    {
        final function testFoo($param)
        {
            aaa();
        }

        /**
         */
        final function testFnc($param)
        {
            $this->setExpectedException(\FooException::class);
            aaa();
        }
    }',
                '<?php
    final class MyTest extends \PHPUnit_Framework_TestCase
    {
        final function testFoo($param)
        {
            aaa();
        }

        /**
         * @expectedException FooException
         */
        final function testFnc($param)
        {
            aaa();
        }
    }',
            ],
            'valid docblock but for property, not method' => [
                '<?php
    final class MyTest extends \PHPUnit_Framework_TestCase
    {
        /**
         * @expectedException FooException
         * @expectedExceptionCode 123
         */
         public $foo;

         public function bar()
         {
             /**
              * @expectedException FooException
              * @expectedExceptionCode 123
              */
             $baz = 1;

             /**
              * @expectedException FooException
              * @expectedExceptionCode 123
              */
             while (false) {}
         }
    }',
            ],
        ];
    }

    /**
     * @param string      $expected
     * @param null|string $input
     *
     * @dataProvider provideMessyWhitespacesCases
     */
    public function testMessyWhitespaces($expected, $input = null)
    {
        $expected = str_replace(['    ', "\n"], ["\t", "\r\n"], $expected);
        if (null !== $input) {
            $input = str_replace(['    ', "\n"], ["\t", "\r\n"], $input);
        }

        $this->fixer->setWhitespacesConfig(new WhitespacesFixerConfig("\t", "\r\n"));

        $this->doTest($expected, $input);
    }

    public function provideMessyWhitespacesCases()
    {
        return [
            [
                '<?php
    final class MyTest extends \PHPUnit_Framework_TestCase
    {
        /**
         */
        public function testFnc()
        {
            $this->setExpectedException(\FooException::class, \'foo\', 123);
            aaa();
        }
    }',
                '<?php
    final class MyTest extends \PHPUnit_Framework_TestCase
    {
        /**
         * @expectedException FooException
         * @expectedExceptionMessage foo
         * @expectedExceptionCode 123
         */
        public function testFnc()
        {
            aaa();
        }
    }',
            ],
        ];
    }
}
