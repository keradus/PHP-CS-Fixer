<?php

/*
 * This file is part of the PHP CS utility.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Symfony\CS\Tests\Fixer\Symfony;

use Symfony\CS\Tests\Fixer\AbstractFixerTestBase;

/**
 * @author Bram Gotink <bram@gotink.me>
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 */
class YodaConditionsFixerTest extends AbstractFixerTestBase
{
    /**
     * @dataProvider provideExamples
     */
    public function testFixer($expected, $input = null)
    {
        $this->makeTest($expected, $input);
    }

    public function provideExamples()
    {
        return array(
            // valid conditions that should not be changed
            array('<?php return array(2) == $a;'),
            array('<?php return array($a) == $a;'),
            array('<?php return $this->getStuff() === $myVarirable;'),
            array('<?php return 2 * $myVar % 3 === $a;'),
            array('<?php return ($a & self::MY_BITMASK) === $a;'),
            array('<?php return count($this->array[$var]) === $a;'),

            // simple non-Yoda conditions that need to be fixed
            array(
                '<?php return array(2) == $a;',
                '<?php return $a == array(2);',
            ),
            array(
                '<?php return array($a) == $a;',
                '<?php return $a == array($a);',
            ),
            array(
                '<?php return $this->getStuff() === $myVarirable;',
                '<?php return $myVarirable === $this->getStuff();',
            ),
            array(
                '<?php return 2 * $myVar % 3 === $a;',
                '<?php return $a === 2 * $myVar % 3;',
            ),
            array(
                '<?php return ($a & self::MY_BITMASK) === $a;',
                '<?php return $a === ($a & self::MY_BITMASK);',
            ),
            array(
                '<?php return count($this->array[$var]) === $a;',
                '<?php return $a === count($this->array[$var]);',
            ),
            array(
                '<?php return 2 == $a;',
                '<?php return $a == 2;',
            ),
            array(
                '<?php return 2 == ($a);',
                '<?php return ($a) == 2;',
            ),
            array(
                '<?php return null == $a[2];',
                '<?php return $a[2] == null;',
            ),
            array(
                '<?php return "" === $this->myArray[$index];',
                '<?php return $this->myArray[$index] === "";',
            ),
            array(
                '<?php return "" === $this->myArray[$index]->a;',
                '<?php return $this->myArray[$index]->a === "";',
            ),
            array(
                '<?php return "" === $this->myObject->{$index};',
                '<?php return $this->myObject->{$index} === "";',
            ),
            array(
                '<?php return "" === $this->myObject->{$index}->a;',
                '<?php return $this->myObject->{$index}->a === "";',
            ),
            array(
                '<?php return self::MY_CONST === self::$myVariable;',
                '<?php return self::$myVariable === self::MY_CONST;',
            ),
            array(
                '<?php return null === $a[0 === $b ? $c : $d];',
                '<?php return $a[$b === 0 ? $c : $d] === null;',
            ),
            array(
                '<?php return null === $this->{null === $a ? "a" : "b"};',
                '<?php return $this->{$a === null ? "a" : "b"} === null;',
            ),

            // complex code sample
            array(
                '<?php
if ($a == $b) {
    return null === $b ? (null === $a ? 0 : 0 === $a->b) : 0 === $b->a;
} else {
    if ($c === (null === $b)) {
        return false === $d;
    }
}',
                '<?php
if ($a == $b) {
    return $b === null ? ($a === null ? 0 : $a->b === 0) : $b->a === 0;
} else {
    if ($c === ($b === null)) {
        return $d === false;
    }
}',
            ),
        );
    }

    /**
     * @dataProvider provideExamplesPhp56
     * @requires PHP 5.6
     */
    public function testFixerPhp56($expected, $input = null)
    {
        $this->makeTest($expected, $input);
    }

    public function provideExamplesPhp56()
    {
        return array(
            // simple non-Yoda conditions that need to be fixed
            array(
                '<?php $a **= 4 === $b ? 2 : 3;',
                '<?php $a **= $b === 4 ? 2 : 3;',
            ),
        );
    }
}
