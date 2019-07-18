<?php

/** @see MAChitgarha\Component\Json */
namespace MAChitgarha\UnitTest\Json;

use PHPUnit\Framework\TestCase;
use MAChitgarha\Component\Json;

class ScalarDataTest extends TestCase
{
    /**
     * Tests JSON::isCountable(), JSON::toCountable() and JSON::count() methods.
     * @dataProvider scalarJsonProvider
     */
    public function testCountable(JSON $json)
    {
        $this->assertFalse($json->isCountable());
        $this->assertTrue($json->toCountable()->isCountable());
        $this->assertEquals(1, $json->count());
    }

    public function scalarJsonProvider()
    {
        $json = new JSON(random_bytes(10));

        return [
            [clone $json]
        ];
    }
}
