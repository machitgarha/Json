<?php

namespace MAChitgarha\Json\Components;

use MAChitgarha\Json\Exceptions\InvalidArgumentException;
use MAChitgarha\Json\Options\InitOption;

class Data
{
    /**
     * Default value for options parameter passed to Data::__construct().
     * @see self::__construct()
     * @var int
     */
    protected const DEFAULT_OPTIONS = 3;

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

    protected $isScalar = false;

    /**
     * Initializes the given data.
     *
     * @param string|array|object
     * @todo Add support for options parameter.
     * @return void
     */
    public function __construct($data, int $options = null)
    {
        if (is_resource($data)) {
            throw new InvalidArgumentException("Data must not be a resource");
        }

        // Default value
        if ($options === null) {
            $options = static::DEFAULT_OPTIONS;
        }

        if (is_string($data)) {
            if ($options & InitOption::SCALAR_DATA) {
                $this->decoded = $data;
                $this->isScalar = true;
            } else {
                $this->encoded = $data;
                // Unknown, we don't know
                // TODO: Maybe, implement this
                $this->isScalar = null;
            }
        } else {
            $this->decoded = $data;
            $this->isScalar = is_scalar($data);
        }
    }
}
