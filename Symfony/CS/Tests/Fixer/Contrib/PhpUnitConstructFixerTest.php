<?php

/*
 * This file is part of the PHP CS utility.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Symfony\CS\Tests\Fixer\Contrib;

use Symfony\CS\Tests\Fixer\AbstractFixerTestBase;

/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 */
class PhpUnitConstructFixerTest extends AbstractFixerTestBase
{
    /**
     * @dataProvider provideTestFixCases
     */
    public function testFix($expected, $input = null)
    {
        $this->makeTest($expected, $input);
    }

    /**
     * @dataProvider provideTestFixCases
     */
    public function testFixWithDisabled($expected, $input = null)
    {
        $fixer = $this->getFixer();
        $fixer->configure(array(
            'assertSame' => false,
            'assertEquals' => false,
            'assertNotEquals' => false,
            'assertNotSame' => false,
        ));

        $this->makeTest($expected, null, null, $fixer);
    }

    public function provideTestFixCases()
    {
        $cases = array(
            array('<?php $sth->assertSame(true, $foo);'),
            array(
                '<?php
    $this->assertTrue(
        $a,
        "foo" . $bar
    );',
                '<?php
    $this->assertSame(
        true,
        $a,
        "foo" . $bar
    );',
            ),
        );

        $types = array('true', 'false', 'null');
        $functionTypes = array('Same' => true, 'NotSame' => false, 'Equals' => true, 'NotEquals' => false);
        $fromTemplate = '<?php $this->assert%s(%s, $a, "%s", "%s")';
        $toTemplate = '<?php $this->assert%s%s($a, "%s", "%s")';

        for($i = count($types)-1; $i >= 0; --$i) {
            foreach($functionTypes as $type => $positive) {
                $from = sprintf($fromTemplate, $type, $types[$i], $types[$i], $types[$i]);
                if ($positive) {
                    $to = sprintf($toTemplate, '', ucfirst($types[$i]), $types[$i], $types[$i]);
                } else {
                    $to = sprintf($toTemplate, 'Not', ucfirst($types[$i]), $types[$i], $types[$i]);
                }

                $cases[] = array($to, $from);
            }
        }

        return $cases;
    }
}
