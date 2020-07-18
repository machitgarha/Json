<?php

/** @see MAChitgarha\Json\Component\Json */
namespace MAChitgarha\Json\UnitTest;

use PHPUnit\Framework\TestCase;
use MAChitgarha\Json\Component\Json;

class FeatureTest extends TestCase
{
    /** @see Json::extractKeysFromIndex() */
    public function testEscapedDelimiters()
    {
        $json = new Json([
            "fileInfo" => [
                "data.json" => "Complex"
            ]
        ]);

        $this->assertEquals("Complex", $json->get("fileInfo.data\.json"));
    }
}
