<?php

namespace MAChitgarha\Json\Options;

/**
 * One-time options container for Json constructor.
 * @todo Improve documentation.
 */
class InitOption extends OptionContainer
{
    /**
     * Force a string to be supposed as scalar (i.e. basic string), not as a JSON
     * string; in other words, it is in the decoded form. This option must only be
     * passed when the passed data is string, otherwise you will get an exception.
     * @var int
     */
    public const SCALAR_DATA = 1;
}
