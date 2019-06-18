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
use MAChitgarha\Exception\JSON\UncountableValueException;

class UncountableValueExceptionTest extends TestCase
{
    /** @var JSON */
    protected $json;

    protected function setUp()
    {
        $this->expectException(UncountableValueException::class);

        // Set up fixtures
        $this->json = new JSON();
    }

    public function testCount()
    {
        $this->json->count("0");
    }

    public function testIterate()
    {
        foreach ($this->json->iterate("0") as $i);
    }

    public function testPush()
    {
        $this->json->push(7, "0");
    }

    public function testPop()
    {
        $this->json->pop("0");
    }
}
