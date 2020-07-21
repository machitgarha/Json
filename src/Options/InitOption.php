<?php

namespace MAChitgarha\Json\Options;

/**
 * One-time options container for Json constructor.
 * @todo Improve documentation.
 */
class InitOption
{
    /**
     * Data is scalar. So, if the data passed is of string type, it's a basic string,
     * not a JSON string (i.e. must be decoded). This option must only be passed when the
     * passed data is string, otherwise you will get an exception.
     * @var int
     */
    public const SCALAR_DATA = 1;
}
