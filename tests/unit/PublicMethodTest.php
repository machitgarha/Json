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

class PublicMethodTest extends TestCase
{
    /** @var JSON The sample JSON which loads from a file. */
    public static $sampleJson;
    /** @var array The data of the sample JSON as an array. */
    public static $sampleData;

    private static function getSampleJsonAndData()
    {
        static $sampleJson, $sampleData;

        if (!isset($sampleJson)) {
            $sampleJson = new JSON(file_get_contents(__DIR__ . "/../json/apps.json"));
            $sampleData = $sampleJson->getDataAsArray();
        }

        return [
            clone $sampleJson,
            $sampleData,
        ];
    }

    public static function setUpBeforeClass()
    {
        list(self::$sampleJson, self::$sampleData) = self::getSampleJsonAndData();
    }

    /**
     * Tests JSON::getData*() methods.
     * @dataProvider expectedGetDataReturnValuesProvider
     */
    public function testGetData($data, $asJson, $asArray, $asObject, $asFullObject)
    {
        $json = new JSON($data);

        $this->assertEquals($asJson, $json->getDataAsJsonString());
        $this->assertEquals($asArray, $json->getDataAsArray());
        $this->assertEquals($asObject, $json->getDataAsObject());
        $this->assertEquals($asFullObject, $json->getDataAsFullObject());
    }

    /** @dataProvider indexValuePairsProvider */
    public function testSetAndGet(string $index, $value)
    {
        $json = new JSON();

        $json->set($value, $index);
        $this->assertEquals($value, $json->get($index));
    }

    /** @dataProvider indexValuePairsProvider */
    public function testSetAndGetUsingArrayAccess(string $index, $value)
    {
        $json = new JSON();

        $json[$index] = $value;
        $this->assertEquals($value, $json[$index]);
    }

    /** @dataProvider indexValuePairsProvider */
    public function testIsSet(string $index, $value)
    {
        $json = new JSON();

        $json->set($value, $index);

        $this->assertTrue($json->isSet($index));
        $this->assertTrue(isset($json[$index]));
    }

    /** @dataProvider indexValuePairsProvider */
    public function testUnset(string $index, $value)
    {
        $json = new JSON();

        $json->set($value, $index);
        $json->unset($index);

        $this->assertFalse(isset($json[$index]));

        $json->set($value, $index);
        unset($json[$index]);

        $this->assertFalse(isset($json[$index]));
    }

    public function testIterate()
    {
        $json = clone self::$sampleJson;

        foreach ($json->iterate("apps.browsers") as $i => $browserName) {
            $this->assertEquals($browserName, $json->get("apps.browsers.$i"));
        }
    }

    /**
     * Tests JSON::isCountable() and JSON::count() methods.
     * @dataProvider arrayAndJsonProvider
     */
    public function testCountable(JSON $json, string $index, array $array)
    {
        $this->assertTrue($json->isCountable($index));
        $this->assertEquals(count($array), $json->count($index));
    }

    public function testPushPopShiftUnshift()
    {
        $json = clone self::$sampleJson;

        $index = "apps.ides.JetBrains";
        $value = "CLion";

        $this->assertEquals($value, $json->push($value, $index)->pop($index));
        $this->assertEquals($value, $json->unshift($value, $index)->shift($index));
    }

    public function testExchange()
    {
        $json = clone self::$sampleJson;

        $this->assertEquals(self::$sampleData, $json->exchange([]));
        $this->assertEquals([], $json->get());
    }

    /**
     * Tests JSON::getValues(), JSON::getKeys(), JSON::getRandomValue(), getRandomKey(),
     * JSON::getRandomValues() and JSON::getRandomKeys().
     * @dataProvider arrayAndJsonProvider
     */
    public function testGettingRandomValuesAndKeys(JSON $json, string $index, array $array)
    {
        $this->assertTrue(in_array($json->getRandomValue($index), $array));
        $this->assertArrayHasKey($json->getRandomKey($index), $array);

        $randomCount = 2;
        $randomValues = $json->getRandomValues($randomCount, $index);
        $randomKeys = $json->getRandomKeys($randomCount, $index);
        $randomSubset = $json->getRandomSubset($randomCount, $index);

        $this->assertEmpty(array_diff($randomValues, array_values($array)));
        $this->assertEmpty(array_diff($randomKeys, array_keys($array)));
        $this->assertArraySubset($randomSubset, $array);
    }

    /**
     * Tests JSON::getValues() and JSON::getKeys().
     * @dataProvider arrayAndJsonProvider
     */
    public function testGetValuesAndKeys(JSON $json, string $index, array $array)
    {
        $this->assertEquals(array_values($array), $json->getValues($index));
        $this->assertEquals(array_keys($array), $json->getKeys($index));
    }

    /** @dataProvider arrayAndJsonProvider */
    public function testDifference(JSON $json, string $index, array $array)
    {
        $diff = $json[$index];

        $this->assertEmpty($json->difference($diff, false, $index)->get($index));
        $this->assertEmpty($json->difference($diff, true, $index)->get($index));
    }

    public function testFilter()
    {
        $json = new JSON([
            220,
            4,
            24
        ]);

        $this->assertEmpty($json->filter(function ($value) {
            return $value % 2 !== 0;
        })->get());
    }

    public function testFlip()
    {
        $json = new JSON();
        $this->assertEquals(1, $json->fill(0, 1000, 1)->flipValuesAndKeys()->count());
    }

    public function testReduce()
    {
        $this->assertIsString(self::$sampleJson->reduce(function ($carry, $value) {
            return "$carry, $value";
        }, "apps.others"));
    }

    public function testFill()
    {
        $json = new JSON();
        $this->assertEquals(1000, $json->fill(0, 1000, 4)->count());
    }

    /** Provides index and values pairs. */
    public function indexValuePairsProvider()
    {
        return [
            [0, []],
            ["id", 14],
            ["error.type", "Exception"],
            ["", "An empty key (index)"]
        ];
    }

    public function arrayAndJsonProvider()
    {
        list($json, $array) = self::getSampleJsonAndData();

        return [
            [$json, "apps.others", $array["apps"]["others"]]
        ];
    }

    /** Provider for JSON::getData*() methods. */
    public function expectedGetDataReturnValuesProvider()
    {
        // JSON data that we expect
        return [
            [
                [[]],
                "[[]]",
                [[]],
                [[]],
                (object)[(object)[]]
            ],
            [
                '{"instance":"JSON"}',
                '{"instance":"JSON"}',
                ["instance" => "JSON"],
                (object)["instance" => "JSON"],
                (object)["instance" => "JSON"]
            ]
        ];
    }
}
