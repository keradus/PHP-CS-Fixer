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

use PhpCsFixer\Test\AbstractFixerTestCase;

/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 *
 * @covers \PhpCsFixer\Fixer\PhpUnit\PhpUnitExpectationFixer
 */
final class PhpUnitExpectationFixerTest extends AbstractFixerTestCase
{
    /**
     * @param string      $expected
     * @param null|string $input
     *
     * @dataProvider provideTestFixCases
     */
    public function testFix($expected, $input = null)
    {
        $this->doTest($expected, $input);
    }

    public function provideTestFixCases()
    {
        return [
            [
                '<?php
    final class MyTest extends \PHPUnit_Framework_TestCase
    {
        function testFnc()
        {
            aaa();
            $this->expectException(\'RuntimeException\');
            $this->expectExceptionMessage(\'msg\'/*B*/);
            $this->expectExceptionCode(/*C*/123);
            zzz();
        }
    }',
                '<?php
    final class MyTest extends \PHPUnit_Framework_TestCase
    {
        function testFnc()
        {
            aaa();
            $this->setExpectedException(\'RuntimeException\', \'msg\'/*B*/, /*C*/123);
            zzz();
        }
    }',
            ],
            [
                '<?php
    final class MyTest extends \PHPUnit_Framework_TestCase
    {
        function testFnc()
        {
            aaa();
            $this->expectException(\'RuntimeException\');
            $this->expectExceptionMessage(\'msg\');
            $this->expectExceptionCode(/*C*/123);
            zzz();
        }
    }',
                '<?php
    final class MyTest extends \PHPUnit_Framework_TestCase
    {
        function testFnc()
        {
            aaa();
            $this->setExpectedException(\'RuntimeException\',\'msg\',/*C*/123);
            zzz();
        }
    }',
            ],
        ];
    }
}
