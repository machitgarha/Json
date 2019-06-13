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

/**
 * Expect \InvalidArgumentException in all of these tests.
 */
class InvalidArgumentExceptionTest extends TestCase
{
    /** @var JSON */
    protected $json;

    protected function setUp()
    {
        $this->expectException(\InvalidArgumentException::class);

        // Set up fixtures
        $this->json = new JSON([]);
    }

    /** Tests invalid data. */
    public function testInvalidData()
    {
        new JSON(fopen(__FILE__, "r"));
    }

    /**
     * Calling a method with bad arguments.
     * @todo Create a test for each method call, statically.
     * @dataProvider badMethodCallProvider
     */
    public function testMethodCall(string $methodName, array $arguments)
    {
        $this->json->$methodName(...$arguments);
    }

    public function badMethodCallProvider()
    {
        return [];
    }
}
