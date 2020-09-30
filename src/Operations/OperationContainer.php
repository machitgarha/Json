<?php

namespace MAChitgarha\Json\Operations;

/**
 * A class containing all supported operations a provider could do.
 */
class OperationContainer
{
    /**
     * The operation of converting a JSON string into built-in PHP data.
     * @var string
     */
    public const DECODING = "decoding";

    /**
     * The operation of converting built-in PHP data into a JSON string.
     * @var string
     */
    public const ENCODING = "encoding";

    /**
     * The operation of validating a JSON string.
     * @var string
     */
    public const JSON_LINTING = "json-linting";
}
