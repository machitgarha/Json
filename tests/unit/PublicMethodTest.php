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

class PublicMethodTest extends TestCase
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
    public function testGetData($data, $asJson, $asArray, $asObject, $asFullObject)
    {
        $json = new JSON($data);

        // JSON::getDataAs*() assertions
        $this->assertEquals($asJson, $json->getDataAsJsonString());
        $this->assertEquals($asArray, $json->getDataAsArray());
        $this->assertEquals($asObject, $json->getDataAsObject());
        $this->assertEquals($asFullObject, $json->getDataAsFullObject());
    }

    /** Provider for JSON::getData*() methods. */
    public function expectedGetDataReturnValuesProvider()
    {
        // JSON data that we expect
        return [
            [
                [[]],
                "[[]]",
                [[]],
                [[]],
                (object)[(object)[]]
            ],
            [
                '{"instance":"JSON"}',
                '{"instance":"JSON"}',
                ["instance" => "JSON"],
                (object)["instance" => "JSON"],
                (object)["instance" => "JSON"]
            ]
        ];
    }

    /** @dataProvider indexValuePairsProvider */
    public function testSetAndGet(string $index, $value)
    {
        $json = new JSON();

        $json->set($value, $index);
        $this->assertEquals($value, $json->get($index));
    }

    /** @dataProvider indexValuePairsProvider */
    public function testSetAndGetUsingArrayAccess(string $index, $value)
    {
        $json = new JSON();

        $json[$index] = $value;
        $this->assertEquals($value, $json[$index]);
    }

    /** @dataProvider indexValuePairsProvider */
    public function testIsSet(string $index, $value)
    {
        $json = new JSON();

        $json->set($value, $index);

        $this->assertTrue($json->isSet($index));
        $this->assertTrue(isset($json[$index]));
    }

    /** @dataProvider indexValuePairsProvider */
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

    /** Provides index and values pairs. */
    public function indexValuePairsProvider()
    {
        return [
            [0, []],
            ["id", 14],
            ["error.type", "Exception"],
            ["", "An empty key (index)"]
        ];
    }

    public function testIterate()
    {
        $json = new JSON(self::$sampleData);

        foreach ($json->iterate("apps.browsers") as $i => $browserName) {
            $this->assertEquals($browserName, $json->get("apps.browsers.$i"));
        }
    }

    /** Tests JSON::isCountable() and JSON::count() methods. */
    public function testCountableElements()
    {
        $json = new JSON(self::$sampleData);

        $this->assertTrue($json->isCountable("apps.browsers"));
        $this->assertEquals(
            count(self::$sampleData["apps"]["browsers"]),
            $json->count("apps.browsers")
        );
    }

    public function testPushAndPop()
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
        $this->assertFalse($json->isSet("test"));
    }

    public function testExchange()
    {
        $json = new JSON(self::$sampleData);

        $this->assertEquals(self::$sampleData, $json->exchange([]));
        $this->assertEquals([], $json->get());
    }
}
