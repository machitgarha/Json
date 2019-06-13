<?php

/**
 * Unit tests for MAChitgarha\Component\JSON class.
 *
 * Go to the project's root and run the tests in this way:
 * phpunit --bootstrap vendor/autoload.php tests/unit
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
     * @dataProvider resourceDataProvider
     * @dataProvider invalidJsonDataProvider
     */
    public function testInvalidData($data, $options = 0)
    {
        new JSON($data, $options);
    }

    public function resourceDataProvider()
    {
        return [
            [fopen(__FILE__, "r")],
        ];
    }

    public function invalidJsonDataProvider()
    {
        $invalidJson = [
            "[] // Commenting",
            "{'user_id':1234}",
            "[function () {}]",
            "{color: \"red\"}",
            "[0,1,2,3,4,5,6,]",
        ];

        // Pass the option force JSON class to treat string as a JSON data
        $returnValue = [];
        foreach ($invalidJson as $json) {
            $returnValue[] = [$json, JSON::OPT_TREAT_AS_JSON_STRING];
        }

        return $returnValue;
    }

    /**
     * Calling a method with bad arguments.
     * @todo Create a test for each method call, statically.
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
