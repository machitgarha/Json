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
use MAChitgarha\Exception\JSON\ScalarDataException;

class ScalarDataExceptionTest extends TestCase
{
    /** @var JSON */
    protected $json;

    protected function setUp()
    {
        $this->expectException(ScalarDataException::class);

        // Set up fixtures
        $this->json = new JSON(7);
    }

    public function testGet()
    {
        $this->json->get("apps");
    }

    public function testSet()
    {
        $this->json->set([], "apps");
    }

    public function testUnset()
    {
        $this->json->unset("");
    }

    public function testPush()
    {
        $this->json->push("!");
    }

    public function testPop()
    {
        $this->json->pop();
    }

    public function testCount()
    {
        $this->json->count();
    }
}
