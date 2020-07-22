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
     * The decoded form of data. Non-scalar data (and their sub-data) could be either an
     * array or object.
     * @var mixed
     */
    protected $decoded = null;

    /**
     * Determines whether the data is scalar or not. It can be either a bool or null.
     * Obviously, true means data is scalar (e.g. boolean or integer) and false means
     * otherwise (i.e. array or object). However, the purpose of null is, to show the
     * scalar-ness of data is not obvious (and thus it should be detected).
     * @var ?bool
     */
    protected $isScalar = null;

    /**
     * Initializes the given data.
     *
     * @param mixed $data
     * @param int $options The given options, a combination of InitOption options.
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
