<?php

namespace MAChitgarha\Json\Options;

/**
 * Option provider for Json::encode().
 *
 * @see Json::encode()
 */
class EncodingOption
{
    /**
     * Make the output more well-formatted and human-readable by adding indentions and
     * whitespaces.
     * @var int
     */
    const PRETTY_PRINT = 1;
}
