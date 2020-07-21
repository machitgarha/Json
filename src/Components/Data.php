<?php

namespace MAChitgarha\Json\Components;

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
    }
}
