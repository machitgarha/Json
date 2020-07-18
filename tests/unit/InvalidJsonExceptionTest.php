<?php

/** @see MAChitgarha\Json\Component\Json */
namespace MAChitgarha\Json\UnitTest;

use PHPUnit\Framework\TestCase;
use MAChitgarha\Json\Component\Json;
use MAChitgarha\Json\Exception\InvalidJsonException;
use MAChitgarha\Json\Option\JsonOpt;

class InvalidJsonExceptionTest extends TestCase
{
    /** @var Json */
    protected $json;

    protected function setUp()
    {
        $this->expectException(InvalidJsonException::class);
    }

    /** @dataProvider invalidJsonProvider */
    public function testConstructor(string $data)
    {
        new Json($data, JsonOpt::AS_JSON);
    }

    public function invalidJsonProvider()
    {
        return [
            ["[] // Commenting"],
            ["{'user_id':1234}"],
            ["[function () {}]"],
            ["{color: \"red\"}"],
            ["[0,1,2,3,4,5,6,]"],
        ];
    }
}
