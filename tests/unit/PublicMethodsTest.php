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
class PublicMethodsTest extends TestCase
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
        $data = [new \stdClass()];
        $json = new JSON($data);

        // Expected outputs
        $asJson = "[{}]";
        $asObject = (object)[(object)[]];
        $asArray = [[]];

        return [
            // JSON::getData() assertions
            [$data, $json->getData(JSON::TYPE_DEFAULT)],
            [$asJson, $json->getData(JSON::TYPE_JSON)],
            [$asObject, $json->getData(JSON::TYPE_OBJECT)],        
            [$asArray, $json->getData(JSON::TYPE_ARRAY)],
    
            // JSON::getDataAs*() assertions
            [$asJson, $json->getDataAsJson()],
            [$asArray, $json->getDataAsArray()],
            [$asObject, $json->getDataAsObject()],    
        ];
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
        foreach ($json->iterate("apps.browsers") as $i => $browserName);
            $this->assertEquals($browserName, $json->get("apps.browsers.$i"));
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
