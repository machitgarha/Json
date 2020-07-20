<?php

namespace MAChitgarha\Json\Options;

/**
 * Option container for Json::encode().
 *
 * @see Json::encode()
 */
class EncodingOption extends OptionContainer
{
    /**
     * Make the output more well-formatted and human-readable by adding indentions and
     * whitespaces.
     * @var integer
     */
    const PRETTY_PRINT = 1;
}
