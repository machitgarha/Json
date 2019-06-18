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
use MAChitgarha\Exception\JSON\DataIsScalarException;

class DataIsScalarExceptionTest extends TestCase
{
    /** @var JSON */
    protected $json;

    protected function setUp()
    {
        $this->expectException(DataIsScalarException::class);

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
}
