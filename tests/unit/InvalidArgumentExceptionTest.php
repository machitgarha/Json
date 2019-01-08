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
    /** @var JSON */
    protected $json;

    protected function setUp()
    {
        $this->expectException(\InvalidArgumentException::class);

        // Set up fixtures
        $this->json = new JSON([]);
    }

    /**
     * Tests invalid data.
     * Tests invalid data types and some invalid JSON strings.
     * @todo Import JSON schema.
     * @dataProvider dataTypeProvider
     * @dataProvider invalidJsonProvider
     */
    public function testInvalidData($data)
    {
        new JSON($data);
    }

    public function dataTypeProvider()
    {
        return [
            [null],
            [true],
            [1234],
            [M_PI],
            ["no"],
            // Resources are not valid
            [fopen(__FILE__, "r")]
        ];
    }

    public function invalidJsonProvider()
    {
        return [
            ["[] // Commenting"],
            ["{'user_id':1234}"],
            ["[function () {}]"],
            ["{color: \"red\"}"],
            ["[0,1,2,3,4,5,6,]"],
        ];
    }

    /**
     * Calling a method with bad arguments.
     * @dataProvider badMethodCallProvider
     */
    public function testMethodCall(string $methodName, array $arguments)
    {
        $this->json->$methodName(...$arguments);
    }

    public function badMethodCallProvider()
    {
        return [
            ["getData", [JSON::STRICT_INDEXING]],
            ["set", ["key", "val", JSON::TYPE_DEFAULT]],
            ["set", ["key", "val", JSON::TYPE_JSON]],
        ];
    }
}
