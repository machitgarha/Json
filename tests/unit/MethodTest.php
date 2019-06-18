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
 * Tests all public methods.
 */
class MethodTest extends TestCase
{
    /** @var array A simple sample data to be used as JSON data. */
    public static $sampleData = [
        "apps" => [
            "ides" => [
                "PhpStorm",
                "WebStorm",
                "Visual Studio Code"
            ],
            "browsers" => [
                "Firefox",
                "Chrome",
                "Safari",
                "Opera",
                "Edge"
            ]
        ]
    ];

    /**
     * Tests JSON::getData*() methods.
     * @dataProvider expectedGetDataReturnValuesProvider
     */
    public function testGetData($data, $asJson, $asArray, $asObject)
    {
        $json = new JSON($data);

        // JSON::getData() assertions
        $this->assertEquals(gettype($data), gettype($json->getData(JSON::TYPE_DEFAULT)));
        $this->assertEquals($asJson, $json->getData(JSON::TYPE_JSON_STRING));
        $this->assertEquals($asArray, $json->getData(JSON::TYPE_ARRAY));

        // JSON::getDataAs*() assertions
        $this->assertEquals($asJson, $json->getDataAsJsonString());
        $this->assertEquals($asArray, $json->getDataAsArray());
    }

    /** Provider for JSON::getData*() methods. */
    public function expectedGetDataReturnValuesProvider()
    {
        // JSON data that we expect
        return [
            [
                [new \stdClass()],
                "[[]]",
                [[]],
                (object)[(object)[]]
            ],
            [
                '{"instance":"JSON"}',
                '{"instance":"JSON"}',
                ["instance" => "JSON"],
                (object)["instance" => "JSON"]
            ]
        ];
    }

    /**
     * Tests JSON::set() and JSON::get() methods.
     * @dataProvider indexValuePairsProvider
     */
    public function testSetAndGet(string $index, $value)
    {
        $json = new JSON();

        $json->set($value, $index);
        $this->assertEquals($value, $json->get($index));
    }

    /**
     * Tests JSON::offsetSet() and JSON::offsetGet() methods (i.e. implementing ArrayAccess).
     * @dataProvider indexValuePairsProvider
     */
    public function testArrayAccessSetAndGet(string $index, $value)
    {
        $json = new JSON();

        $json[$index] = $value;
        $this->assertEquals($value, $json[$index]);
    }

    /**
     * Tests JSON::isSet() and JSON::offsetExists() methods (i.e. implementing ArrayAccess).
     * @dataProvider indexValuePairsProvider
     */
    public function testIsSet(string $index, $value)
    {
        $json = new JSON();

        $json->set($value, $index);

        $this->assertTrue($json->isSet($index));
        $this->assertTrue(isset($json[$index]));
    }

    /**
     * Tests JSON::offsetUnset() (i.e. implementing ArrayAccess).
     * @dataProvider indexValuePairsProvider
     */
    public function testUnset(string $index, $value)
    {
        $json = new JSON();

        $json->set($value, $index);
        $json->unset($index);

        $this->assertFalse(isset($json[$index]));

        $json->set($value, $index);
        unset($json[$index]);

        $this->assertFalse(isset($json[$index]));
    }

    /**
     * Provides index and values pairs.
     */
    public function indexValuePairsProvider()
    {
        return [
            [0, []],
            ["id", 14],
            ["error.type", "Exception"],
            ["", "An empty key (index)"]
        ];
    }

    /**
     * Tests JSON::iterate() method.
     * @dataProvider dataProvider
     */
    public function testIterate($data)
    {
        $json = new JSON($data);

        foreach ($json->iterate("apps.browsers") as $i => $browserName) {
            $this->assertEquals($browserName, $json->get("apps.browsers.$i"));
        }

        // Checking the second argument
        $this->assertIsArray($json->iterate("apps", JSON::TYPE_ARRAY)->current());
        $this->assertIsObject($json->iterate("apps", JSON::TYPE_OBJECT)->current());
        $this->assertTrue(JSON::isValidJson($json->iterate("apps", JSON::TYPE_JSON_STRING)->current()));
    }

    /**
     * Tests JSON::isCountable() and JSON::count() methods.
     */
    public function testCountableElements()
    {
        $json = new JSON(self::$sampleData);

        $this->assertTrue($json->isCountable("apps.browsers"));
        $this->assertEquals(
            count(self::$sampleData["apps"]["browsers"]),
            $json->count("apps.browsers")
        );
    }

    /**
     * Tests JSON::push() and JSON::pop() methods.
     */
    public function testPushPop()
    {
        $json = new JSON([
            "test" => "pass"
        ]);

        $var = ["!"];
        $json->push($var);
        $this->assertEquals($var, $json->get("0"));

        $json->pop();
        $this->assertFalse($json->isSet("0"));

        $json->pop();
        $this->assertFalse($json->isset("test"));
    }

    /**
     * Provides prepared data to be used in JSON class.
     */
    public function dataProvider()
    {
        return [
            [
                self::$sampleData
            ]
        ];
    }
}
