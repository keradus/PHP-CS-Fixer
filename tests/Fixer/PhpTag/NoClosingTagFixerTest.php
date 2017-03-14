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

namespace PhpCsFixer\Tests\Fixer\PhpTag;

use PhpCsFixer\Test\AbstractFixerTestCase;

/**
 * @internal
 */
final class NoClosingTagFixerTest extends AbstractFixerTestCase
{
    /**
     * @param string      $expected
     * @param null|string $input
     *
     * @dataProvider provideCasesWithFullOpenTag
     */
    public function testCasesWithFullOpenTag($expected, $input = null)
    {
        $this->doTest($expected, $input);
    }

    /**
     * @param string      $expected
     * @param null|string $input
     *
     * @dataProvider provideCasesWithShortOpenTag
     */
    public function testCasesWithShortOpenTag($expected, $input = null)
    {
        if (!ini_get('short_open_tag')) {
            $this->markTestSkipped('PHP short open tags are not enabled.');

            return;
        }

        $this->doTest($expected, $input);
    }

    public function provideCasesWithFullOpenTag()
    {
        return array(
            array('<?php echo \'Foo\';', '<?php echo \'Foo\'; ?>'),
            array('<?php echo \'Foo\';', '<?php echo \'Foo\';?>'),
            array('<?php echo \'Foo\'; ?> PLAIN TEXT'),
            array('PLAIN TEXT<?php echo \'Foo\'; ?>'),
            array('<?php

echo \'Foo\';',
                  '<?php

echo \'Foo\';

?>',
            ),
            array('<?php echo \'Foo\'; ?>
<p><?php echo \'this is a template\'; ?></p>
<?php echo \'Foo\'; ?>',
            ),
            array('<?php echo "foo";', '<?php echo "foo" ?>'),
            array(
                '<?php
class foo
{
    public function bar()
    {
        echo "Here I am!";
    }
}',
                '<?php
class foo
{
    public function bar()
    {
        echo "Here I am!";
    }
}?>',
            ),
            array(
                '<?php
function bar()
{
    echo "Here I am!";
}',
                '<?php
function bar()
{
    echo "Here I am!";
}?>',
            ),
            array(
                '<?php
if (true) {
    echo "Here I am!";
}',
                '<?php
if (true) {
    echo "Here I am!";
}?>',
            ),
            'Trailing linebreak, priority issue with SingleBlankLineAtEofFixer.' => array(
                '<?php echo 1;',
                "<?php echo 1;\n?>\n",
            ),
            'Trailing comment.' => array(
                '<?php echo 1;// test',
                "<?php echo 1;// test\n?>",
            ),
            'No code' => array(
                '<?php ',
                '<?php ?>',
            ),
            'No code, only comment' => array(
                '<?php /* license */',
                '<?php /* license */ ?>',
            ),
            array(
                '<?php ?>aa',
            ),
        );
    }

    public function provideCasesWithShortOpenTag()
    {
        return array(
            array(
                '<? echo \'Foo\';',
                '<? echo \'Foo\'; ?>',
            ),
            array(
                '<? echo \'Foo\';',
                '<? echo \'Foo\';?>',
                ),
            array('<? echo \'Foo\'; ?>
<p><? echo \'this is a template\'; ?></p>
<? echo \'Foo\'; ?>',
            ),
            array('<? /**/', '<? /**/?>'),
            array('<?= "somestring"; ?> <?= "anotherstring"; ?>'),
            array(
                '<?= 1;',
                '<?= 1; ?>',
            ),
        );
    }
}
