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

namespace PhpCsFixer\Tests\Console\Command;

use PhpCsFixer\Console\Command\DescribeCommand;
use PhpCsFixer\FixerFactory;
use PhpCsFixer\RuleSet;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @author SpacePossum
 *
 * @internal
 */
final class DescribeCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param $setName
     *
     * @dataProvider provideSetNames
     */
    public function testDescribeCommandSet($setName)
    {
        $command = new DescribeCommand();
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array('name' => $setName)
        );

        //echo $commandTester->getDisplay(true);
    }

    public function provideSetNames()
    {
        $cases = array();
        $set = new RuleSet();
        foreach ($set->getSetDefinitionNames() as $name) {
            $cases[] = array($name);
        }

        return $cases;
    }

    /**
     * @param $setName
     *
     * @dataProvider provideRuleNames
     */
    public function testDescribeCommandRule($setName)
    {
        $command = new DescribeCommand();
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array('name' => $setName)
        );

        //echo $commandTester->getDisplay(true);
    }

    public function provideRuleNames()
    {
        $cases = array();
        $fixerFactory = new FixerFactory();
        foreach ($fixerFactory->registerBuiltInFixers()->getFixers() as $fixer) {
            $cases[] = array($fixer->getName());
        }

        return $cases;
    }
}
