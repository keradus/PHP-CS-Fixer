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

namespace PhpCsFixer\Tests\Fixer\FunctionNotation;

use PhpCsFixer\Test\AbstractFixerTestCase;

/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 */
final class AddMissingArgumentTypeFixerTest extends AbstractFixerTestCase
{
    /**
     * @dataProvider testFixProvider
     */
    public function testFix($expected, $input = null)
    {
        $this->doTest($expected, $input);
    }

    public function testFixProvider()
    {
        return array(
            array(
                '<?php
    /**
     * @param Foo      $a
     * @param array    $b
     * @param Foo|null $c
     * @param Foo|Bar  $d
     * @param mixed    $e
     */
    function foo(Foo $a, array $b, Foo $c = null, $d, $e) {}',
                '<?php
    /**
     * @param Foo      $a
     * @param array    $b
     * @param Foo|null $c
     * @param Foo|Bar  $d
     * @param mixed    $e
     */
    function foo($a, array $b, $c, $d, $e) {}',
            ),
        );
    }
}
