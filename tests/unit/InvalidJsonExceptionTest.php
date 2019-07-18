<?php

/** @see MAChitgarha\Component\Json */
namespace MAChitgarha\UnitTest\Json;

use PHPUnit\Framework\TestCase;
use MAChitgarha\Component\Json;
use MAChitgarha\Json\Exception\InvalidJsonException;

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
        new JSON($data, Json::OPT_TREAT_AS_JSON_STRING);
    }

    /** @todo Import JSON schema. */
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
