<?php

/** @see MAChitgarha\Json\Components\Json */
namespace MAChitgarha\Json\UnitTest;

use PHPUnit\Framework\TestCase;
use MAChitgarha\Json\Components\Json;
use MAChitgarha\Json\Exceptions\UncountableValueException;

class UncountableValueExceptionTest extends TestCase
{
    protected function setUp()
    {
        $this->expectException(UncountableValueException::class);
    }

    protected function prependJsonToEveryElement(Json $json, array $array): array
    {
        foreach ($array as &$item) {
            array_unshift($item, $json);
        }
        return $array;
    }

    /** @dataProvider methodsWithUncountableIndexProvider */
    public function testCallMethodsOnUncountableIndex(Json $json, string $funcName, array $args)
    {
        $json->$funcName(...$args);
    }

    /** @dataProvider callMethodsWithIndexesProvider */
    public function testCallMethodsWithIndexes(Json $json, string $funcName, array $args)
    {
        $json->$funcName(...$args);
    }

    /** @dataProvider methodsForCountableDataProvider */
    public function testMethodsDependOnCountableData(Json $json, string $funcName, array $args = [])
    {
        $json->$funcName(...$args);
    }

    public function callMethodsWithIndexesProvider()
    {
        $json = clone new Json("Ubuntu");
        $sampleIndex = "appName";
        $sampleValue = "Terminal";

        return $this->prependJsonToEveryElement($json, [
            ["get", [$sampleIndex]],
            ["set", [$sampleValue, $sampleIndex]],
            ["unset", [$sampleIndex]],
            ["isSet", [$sampleIndex]],
            // ["isCountable", [$sampleIndex]]
        ]);
    }

    public function methodsForCountableDataProvider()
    {
        $json = clone new Json(1400);
        $sampleValue = 1398;

        return $this->prependJsonToEveryElement($json, [
            ["count"],
            ["iterate"],
            ["forEach", [function () {
            }]],
            ["forEachRecursive", [function () {
            }]],
            ["push", [$sampleValue]],
            ["pop"],
            ["shift"],
            ["unshift", [$sampleValue]],
            ["getValues"],
            ["getKeys"],
            ["getRandomValue"],
            ["getRandomKey"],
            ["getRandomElement"],
            ["getRandomValues", [2]],
            ["getRandomKeys", [2]],
            ["getRandomSubset", [2]],
            ["mergeWith", [[]]],
            ["mergeRecursivelyWith", [[]]],
            ["difference", [[]]],
            ["filter"],
            ["flipValuesAndKeys"],
            ["shuffle"],
            ["reverse"],
        ]);
    }

    public function methodsWithUncountableIndexProvider()
    {
        $json = new Json(file_get_contents(__DIR__ . "/../json/apps.json"));

        return $this->prependJsonToEveryElement($json, [
            ["count", ["apps.others.0"]],
            ["pop", ["apps.ides.Microsoft.1"]],
            ["shift", ["apps.others.2.0"]],
        ]);
    }
}
