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

namespace PhpCsFixer\Tests\Test;

use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 */
abstract class TestCase extends BaseTestCase
{
    use PHPUnitGoodPractices_ProphecyInsteadOfMock;
    use PHPUnitGoodPractices_ExpectationViaCodeInsteadOfAnnotation;
    use PHPUnitGoodPractices_StrictAssertion;
}

trait PHPUnitGoodPractices_StrictAssertion
{
    public static function assertEquals($expected, $actual, $message = '', $delta = 0.0, $maxDepth = 10, $canonicalize = false, $ignoreCase = false)
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);

        // internally, PHPUnit calls `assertEquals` instead of `assertSame` internally, we allow that
        if ('PHPUnit_Framework_Assert' !== $trace[1]['class']) {
            var_dump($trace[1]);
            PHPUnitGoodPractices_Reporter::report("PHPUnit good practice has been abused.\nUse `->assertSame()` instead of `->assertEquals()`.");
        }

        return call_user_func_array(['parent', __FUNCTION__], func_get_args());
    }
}

trait PHPUnitGoodPractices_ExpectationViaCodeInsteadOfAnnotation
{
    protected function setExpectedExceptionFromAnnotation()
    {
        $expectedException = \PHPUnit_Util_Test::getExpectedException(
            get_class($this),
            $this->getName(false)
        );

        if (false !== $expectedException) {
            PHPUnitGoodPractices_Reporter::report("PHPUnit good practice has been abused.\nUse `->expectException*()` instead of `@expectedException*`.");
            parent::setExpectedExceptionFromAnnotation();
        }

        // no need to call parent method if $expectedException is empty
    }
}

trait PHPUnitGoodPractices_ProphecyInsteadOfMock
{
    protected function getMockObjectGenerator()
    {
        PHPUnitGoodPractices_Reporter::report("PHPUnit good practice has been abused.\nUse `Prophecy` instead of basic `MockObject`.");
        return call_user_func_array(['parent', __FUNCTION__], func_get_args());
    }
}

final class PHPUnitGoodPractices_Reporter
{
    private static $reporter = null;

    private function __construct()
    {
    }

    public static function setReporter($reporter)
    {
        self::$reporter = $reporter;
    }

    public static function report($issue)
    {
        if (null === self::$reporter) {
            self::$reporter = function ($issue) { trigger_error($issue, E_USER_WARNING); };
        }

        $reporter = self::$reporter;
        $reporter($issue);
    }
}