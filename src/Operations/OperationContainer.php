<?php

namespace MAChitgarha\Json\Operations;

/**
 * A class containing all available operations a provider could provide.
 */
class OperationContainer
{
    /**
     * Linting operation.
     *
     * The act of checking a data to be valid for further manipulations.
     *
     * @var string
     */
    public const LINTING = "linting";

    /**
     * Decoding operation.
     *
     * Conversion of a JSON string into a native PHP data.
     *
     * @var string
     */
    public const DECODING = "decoding";

    /**
     * Encoding operation.
     *
     * Conversion of native PHP data into a JSON string.
     *
     * @var string
     */
    public const ENCODING = "encoding";
}
