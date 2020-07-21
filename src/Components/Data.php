<?php

namespace MAChitgarha\Json\Components;

use MAChitgarha\Json\Exception\InvalidArgumentException;

class Data
{
    /**
     * The encoded form of data.
     * @var string
     */
    protected $encoded = null;

    /**
     * The decoded form of data, in ? form.
     * @todo Determine the type of this.
     */
    protected $decoded = null;

    /**
     * Initializes the given data.
     *
     * @param string|array|object
     * @todo Add support for options parameter.
     * @return void
     */
    public function __construct($data)
    {
        if (is_resource($data)) {
            throw new InvalidArgumentException("Data must not be a resource");
        }
    }
}
