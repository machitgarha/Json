<?php

/**
 * Unit tests for MAChitgarha\Component\Json class.
 *
 * Go to the project's root and run the tests in this way:
 * phpunit --bootstrap vendor/autoload.php tests/unit
 *
 * @see MAChitgarha\Component\Json
 */
namespace MAChitgarha\UnitTest\Json;

use PHPUnit\Framework\TestCase;
use MAChitgarha\Component\Json;

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
