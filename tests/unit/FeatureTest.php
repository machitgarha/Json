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

class FeatureTest extends TestCase
{
    /** @see JSON::extractKeysFromIndex() */
    public function testEscapedDelimiters()
    {
        $json = new JSON([
            "fileInfo" => [
                "data.json" => "Complex"
            ]
        ]);

        $this->assertEquals("Complex", $json->get("fileInfo.data\.json"));
    }
}
