<?php

class FooTest extends \PHPUnit_Framework_TestCase {
    public function test_dedicate_assert($foo) {
        $this->assertNull($foo);
        $this->assertInternalType('array', $foo);
        $this->assertTrue(is_nan($foo));
        $this->assertTrue(is_readable($foo));
    }

    /**
     * Foo.
     */
    function test_php_unit_no_expectation_annotation_32()
    {
        $this->setExpectedException(\FooException::class, '', 123);
        bbb();
    }

    /**
     * Foo.
     */
    function test_php_unit_no_expectation_annotation_43()
    {
        $this->setExpectedExceptionRegExp(\FooException::class, '/foo.*$/', 123);
        ccc();
    }
}
