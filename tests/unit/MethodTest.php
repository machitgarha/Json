<?php

/**
 * Unit tests for MAChitgarha\Component\JSON class.
 *
 * Go to the project's root and run the tests in this way:
 * phpunit --bootstrap vendor/autoload.php tests/unit
 * Using the --repeat option is recommended.
 *
 * @see MAChitgarha\Component\JSON
 */
namespace MAChitgarha\UnitTest\JSON;

use PHPUnit\Framework\TestCase;
use MAChitgarha\Component\JSON;

/**
 * Test all public methods.
 */
class MethodTest extends TestCase
{
    /**
     * Tests JSON::getData*() methods.
     * @dataProvider getDataMethodsProvider
     */
    public function testGetData($expected, $actual)
    {
        $this->assertEquals($expected, $actual);
    }

    /** Provider for JSON::getData() and JSON::getData*() methods. */
    public function getDataMethodsProvider()
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
     * Tests JSON set and get methods.
     * @dataProvider setAndGetMethodsProvider
     */
    public function testSetAndGetMethods(string $index, $value, $indexingType = JSON::TYPE_ARRAY)
    {
        $json = new JSON();

        $json->set($index, $value, $indexingType);
        $this->assertTrue($json->isSet($index));
        $this->assertEquals($value, $json->get($index));
    }

    /**
     * Tests JSON offset*() methods (i.e. implementing ArrayAccess).
     * @dataProvider setAndGetMethodsProvider
     */
    public function testArrayAccessMethods(string $index, $value)
    {
        $json = new JSON();

        $json[$index] = $value;
        $this->assertTrue(isset($json[$index]));
        $this->assertEquals($value, $json[$index]);
    }

    /** Provider for JSON::get() and JSON::set() methods. */
    public function setAndGetMethodsProvider()
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
    public function testIterateMethod()
    {
        $json = new JSON(new \stdClass());
        $json->set("apps.browsers", [
            "Firefox",
            "Chrome",
            "Safari",
            "Opera",
            "Edge"
        ]);
        
        // Check for equality
        foreach ($json->iterate("apps.browsers") as $i => $browserName) {
            $this->assertEquals($browserName, $json->get("apps.browsers.$i"));
        }
    }

    /**
     * Tests JSON::isCountable() and JSON::count() methods.
     */
    public function testCountableMethods()
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
    public function testExchangeMethod()
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
