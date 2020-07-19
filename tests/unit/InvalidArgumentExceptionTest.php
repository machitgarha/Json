<?php

/** @see MAChitgarha\Json\Components\Json */
namespace MAChitgarha\Json\UnitTest;

use PHPUnit\Framework\TestCase;
use MAChitgarha\Json\Components\Json;
use MAChitgarha\Json\Exceptions\InvalidArgumentException;
use MAChitgarha\Json\Options\JsonOpt;

class InvalidArgumentExceptionTest extends TestCase
{
    /** @var Json */
    protected $json;

    protected function setUp()
    {
        $this->expectException(InvalidArgumentException::class);

        // Set up fixtures
        $this->json = new Json();
    }

    /**
     * @dataProvider constructorInvalidArgsProvider
     */
    public function testConstructorInvalidArgs($data, int $options = 0)
    {
        new Json($data, $options);
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
