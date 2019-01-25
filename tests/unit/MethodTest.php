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
    /**
     * Tests JSON::getData*() methods.
     * @dataProvider expectedGetDataReturnValuesProvider
     */
    public function testGetData($expected, $actual)
    {
        $this->assertEquals($expected, $actual);
    }

    /** Provider for JSON::getData*() methods. */
    public function expectedGetDataReturnValuesProvider()
    {
        // JSON data that we expect
        $jsonData = [
            [
                "data" => [new \stdClass()],
                "asJson" => "[{}]",
                "asArray" => [[]],
                "asObject" => (object)[(object)[]]
            ],
            [
                "data" => '{"instance":"JSON"}',
                "asJson" => '{"instance":"JSON"}',
                "asArray" => ["instance" => "JSON"],
                "asObject" => (object)["instance" => "JSON"]
            ]
        ];

        $jsonValidationProviderData = [];
        foreach ($jsonData as $jsonDatum) {
            $data = $jsonDatum["data"];
            $json = new JSON($data);

            $asJson = $jsonDatum["asJson"];
            $asArray = $jsonDatum["asArray"];
            $asObject = $jsonDatum["asObject"];
            
            // Add each of the JSON data as a provider template
            $jsonValidationProviderData = array_merge($jsonValidationProviderData, [
                // JSON::getData() assertions
                [$data, $json->getData(JSON::TYPE_DEFAULT)],
                [$asJson, $json->getData(JSON::TYPE_JSON)],
                [$asArray, $json->getData(JSON::TYPE_ARRAY)],
                [$asObject, $json->getData(JSON::TYPE_OBJECT)],

                // JSON::getDataAs*() assertions
                [$asJson, $json->getDataAsJson()],
                [$asArray, $json->getDataAsArray()],
                [$asObject, $json->getDataAsObject()]
            ]);
        }

        return $jsonValidationProviderData;
    }

    /**
     * Tests JSON::set() and JSON::get() methods.
     * @dataProvider indexValuePairsProvider
     */
    public function testSetAndGet(string $index, $value, $indexingType = JSON::TYPE_ARRAY)
    {
        $json = new JSON();

        $json->set($index, $value, $indexingType);
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

        $json->set($index, $value);

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

        $json->set($index, $value);
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

    /**
     * Tests JSON::iterate() method.
     */
    public function testIterate()
    {
        $json = new JSON(new \stdClass());

        $json->set("apps.browsers", [
            "Firefox",
            "Chrome",
            "Safari",
            "Opera",
            "Edge"
        ]);
        
        foreach ($json->iterate("apps.browsers") as $i => $browserName) {
            $this->assertEquals($browserName, $json->get("apps.browsers.$i"));
        }
    }

    /**
     * Tests JSON::isCountable() and JSON::count() methods.
     */
    public function testCountableElements()
    {
        $json = new JSON();

        $json->set("apps.browsers", [
            "Firefox",
            "Chrome",
            "Safari",
            "Opera",
            "Edge"
        ]);

        $this->assertTrue($json->isCountable("apps.browsers"));
        $this->assertEquals(5, $json->count("apps.browsers"));
    }

    /**
     * Tests JSON::exchange() method.
     */
    public function testExchange()
    {
        $json = new JSON();

        $json->exchange($data = [
            "apps" => [
                "PhpStorm",
                "WebStorm",
                "Chromium",
                "WireShark"
            ]
        ]);
        
        $this->assertEquals($data, $json->getData());
    }
}
