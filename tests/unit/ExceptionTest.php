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
 * Expect \Exception in all of these tests.
 */
class ExceptionTest extends TestCase
{
    /** @var JSON */
    protected $json;

    protected function setUp()
    {
        $this->expectException(\Exception::class);
    }

    /**
     * Tests invalid JSON strings.
     *
     * @dataProvider invalidJsonProvider
     */
    public function testInvalidJsonStrings(string $data)
    {
        new JSON($data, JSON::OPT_TREAT_AS_JSON_STRING);
    }

    /** @todo Import JSON schema. */
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
}
