<?php

/**
 * Unit tests for MAChitgarha\Component\Json class.
 *
 * Go to the project's root and run the tests in this way:
 * phpunit --bootstrap vendor/autoload.php tests/unit
 *
 * @see MAChitgarha\Component\Json
 */
namespace MAChitgarha\UnitTest\Json;

use PHPUnit\Framework\TestCase;
use MAChitgarha\Component\Json;
use MAChitgarha\Json\Exception\InvalidArgumentException;

class InvalidArgumentExceptionTest extends TestCase
{
    /** @var Json */
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
            [22022, JSON::OPT_TREAT_AS_JSON_STRING],
        ];
    }
}
