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
use MAChitgarha\Exception\JSON\InvalidArgumentException;

class InvalidArgumentExceptionTest extends TestCase
{
    /** @var JSON */
    protected $json;

    protected function setUp()
    {
        $this->expectException(InvalidArgumentException::class);

        // Set up fixtures
        $this->json = new JSON();
    }

    /**
     * @dataProvider constructorInvalidArgsProvider
     */
    public function testConstructorInvalidArgs($data, int $options = 0)
    {
        new JSON($data, $options);
    }

    public function constructorInvalidArgsProvider()
    {
        return [
            // Invalid data
            [fopen(__FILE__, "r")],

            // Invalid combination of data and options
            [false, JSON::OPT_TREAT_AS_STRING],
            [51004, JSON::OPT_TREAT_AS_JSON_STRING],
            [4.262, JSON::OPT_TREAT_AS_STRING | JSON::OPT_TREAT_AS_JSON_STRING],
        ];
    }

    public function testIterate()
    {
        foreach ($this->json->iterate(null, JSON::TYPE_SCALAR) as $value) {}
    }

    /**
     * @dataProvider badTypeProvider
     */
    public function testGetData(int $type)
    {
        $this->json->getData($type);
    }

    public function badTypeProvider()
    {
        return [
            [JSON::TYPE_JSON_CLASS],
            [JSON::TYPE_SCALAR],
        ];
    }
}
