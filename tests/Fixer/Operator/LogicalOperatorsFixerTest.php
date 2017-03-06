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

namespace PhpCsFixer\Tests\Fixer\Operator;

use PhpCsFixer\Test\AbstractFixerTestCase;

/**
 * @author Haralan Dobrev <hkdobrev@gmail.com>
 *
 * @internal
 */
final class LogicalOperatorsFixerTest extends AbstractFixerTestCase
{
    /**
     * @dataProvider provideFixCases
     *
     * @param string              $expected
     * @param string              $input
     * @param array<string, bool> $configuration
     */
    public function testFix($expected, $input, $configuration)
    {
        $this->fixer->configure($configuration);
        $this->doTest($expected, $input);
    }

    public function provideFixCases()
    {
        return array(
            array(
                '<?php if ($a == "foo" && $b == "bar") {}',
                '<?php if ($a == "foo" and $b == "bar") {}',
                array('use_keywords' => false),
            ),
            array(
                '<?php if ($a == "foo" || $b == "bar") {}',
                '<?php if ($a == "foo" or $b == "bar") {}',
                array('use_keywords' => false),
            ),
            array(
                '<?php if ($a == "foo" && ($b == "bar" || $c == "baz")) {}',
                '<?php if ($a == "foo" and ($b == "bar" or $c == "baz")) {}',
                array('use_keywords' => false),
            ),
            array(
                '<?php if ($a == "foo" && ($b == "bar" || $c == "baz")) {}',
                '<?php if ($a == "foo" and ($b == "bar" || $c == "baz")) {}',
                array('use_keywords' => false),
            ),
            array(
                '<?php if ($a == "foo" && ($b == "bar" || $c == "baz")) {}',
                '<?php if ($a == "foo" && ($b == "bar" or $c == "baz")) {}',
                array('use_keywords' => false),
            ),
            array(
                '<?php if ($a == "foo" and $b == "bar") {}',
                '<?php if ($a == "foo" && $b == "bar") {}',
                array('use_keywords' => true),
            ),
            array(
                '<?php if ($a == "foo" or $b == "bar") {}',
                '<?php if ($a == "foo" || $b == "bar") {}',
                array('use_keywords' => true),
            ),
            array(
                '<?php if ($a == "foo" and ($b == "bar" or $c == "baz")) {}',
                '<?php if ($a == "foo" && ($b == "bar" || $c == "baz")) {}',
                array('use_keywords' => true),
            ),
            array(
                '<?php if ($a == "foo" and ($b == "bar" or $c == "baz")) {}',
                '<?php if ($a == "foo" && ($b == "bar" or $c == "baz")) {}',
                array('use_keywords' => true),
            ),
            array(
                '<?php if ($a == "foo" and ($b == "bar" or $c == "baz")) {}',
                '<?php if ($a == "foo" and ($b == "bar" || $c == "baz")) {}',
                array('use_keywords' => true),
            ),
        );
    }
}
