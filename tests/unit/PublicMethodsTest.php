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
    /** @var JSON[] */
    protected $jsons = [];

    protected function setUp()
    {
        $this->jsons = [
            new JSON([new \stdClass()])
        ];
    }

    /**
     * Test for equality, using providers.
     * @dataProvider getDataMethodsProvider
     */
    public function testAssertEquals($expected, $actual)
    {
        $this->assertEquals($expected, $actual);
    }

    /**
     * Provider for JSON::getData() and JSON::getData*() methods.
     */
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
}
