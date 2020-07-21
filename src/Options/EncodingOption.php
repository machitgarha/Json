<?php

namespace MAChitgarha\Json\Options;

/**
 * Option container for Json::encode().
 *
 * @todo Start options from 2 ** 32 instead of 1, because of options support by child
 * classes for other providers.
 * @see Json::encode()
 */
class EncodingOption extends OptionContainer
{
    /**
     * Make the output more well-formatted and human-readable by adding indentions and
     * whitespaces.
     * @var int
     */
    public const PRETTY_PRINT = 1;
}
