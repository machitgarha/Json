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
 * Test all public methods.
 */
class PublicMethodsTest extends TestCase
{
    /** Test JSON::getData() and JSON::getDataAs*() methods. */
    public function testGetData()
    {
        $data = [new \stdClass()];
        $json = new JSON($data);

        // Expected outputs
        $asJson = "[{}]";
        $asObject = (object)[(object)[]];
        $asArray = [[]];

        // JSON::getData() assertions
        $this->assertEquals($data, $json->getData(JSON::TYPE_DEFAULT));
        $this->assertEquals($asJson, $json->getData(JSON::TYPE_JSON));
        $this->assertEquals($asObject, $json->getData(JSON::TYPE_OBJECT));        
        $this->assertEquals($asArray, $json->getData(JSON::TYPE_ARRAY));

        // JSON::getDataAs*() assertions
        $this->assertEquals($asJson, $json->getDataAsJson());
        $this->assertEquals($asArray, $json->getDataAsArray());
        $this->assertEquals($asObject, $json->getDataAsObject());
    }
}
