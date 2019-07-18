<?php

/** @see MAChitgarha\Component\Json */
namespace MAChitgarha\UnitTest\Json;

use PHPUnit\Framework\TestCase;
use MAChitgarha\Component\Json;

class FeatureTest extends TestCase
{
    /** @see Json::extractKeysFromIndex() */
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
