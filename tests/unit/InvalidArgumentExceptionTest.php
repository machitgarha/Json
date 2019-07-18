<?php

/** @see MAChitgarha\Component\Json */
namespace MAChitgarha\UnitTest\Json;

use PHPUnit\Framework\TestCase;
use MAChitgarha\Component\Json;
use MAChitgarha\Json\Exception\InvalidArgumentException;
use MAChitgarha\Json\Option\JsonOpt;

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
            [22022, JsonOpt::AS_JSON],
        ];
    }
}
