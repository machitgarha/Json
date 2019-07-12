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
    protected function setUp()
    {
        $this->expectException(ScalarDataException::class);
    }

    /** @dataProvider callMethodsWithIndexesProvider */
    public function testCallingValidMethodsWithIndexes(JSON $json, string $funcName, array $args)
    {
        $json->$funcName(...$args);
    }

    /** @dataProvider methodsForCountableDataProvider */
    public function testMethodsDependOnCountableData(JSON $json, string $funcName, array $args)
    {
        $json->$funcName(...$args);
    }

    public function callMethodsWithIndexesProvider()
    {
        $json = clone new JSON("Ubuntu");
        $sampleIndex = "appName";
        $sampleValue = "Terminal";

        return [
            [$json, "get", [$sampleIndex]],
            [$json, "set", [$sampleValue, $sampleIndex]],
            [$json, "unset", [$sampleIndex]],
        ];
    }

    public function methodsForCountableDataProvider()
    {
        $json = clone new JSON(1400);
        $sampleIndex = "year";
        $sampleValue = 1398;

        return [
        ];     
    }
}
