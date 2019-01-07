<?php

/** 
 * Unit tests for MAChitgarha\Component\JSON class.
 *
 * Go to the project's root and run the tests in this way:
 * phpunit --bootstrap vendor/autoload.php tests/unit
 * Recommended: Use the --repeat option (with the value of 10) to run all possible cases.
 * 
 * @see MAChitgarha\Component\JSON
 */
namespace MAChitgarha\UnitTest\JSON;

use PHPUnit\Framework\TestCase;
use MAChitgarha\Component\JSON;

/**
 * Expect \InvalidArgumentException in all of these tests.
 */
class InvalidArgumentExceptionTest extends TestCase
{
    protected function setUp()
    {
        $this->expectException(\InvalidArgumentException::class);
    }

    public function testBooleanAsData()
    {
        new JSON(true);
    }

    public function testIntegerAsData()
    {
        new JSON(2);
    }

    public function testFloatAsData()
    {
        new JSON(M_PI);
    }

    public function testSimpleStringAsData()
    {
        new JSON("String");
    }

    public function testInvalidJsonAsData()
    {
        new JSON("[] // Comment");
    }

    public function testInvalidJsonAsData2()
    {
        new JSON("{'id': 0}");
    }

    public function testInvalidJsonAsData3()
    {
        new JSON("[function () {}]");
    }

    public function testInvalidJsonAsData4()
    {
        new JSON("{color: \"red\"}");
    }

    public function testInvalidJsonAsData5()
    {
        new JSON("[0: 2]");
    }
}