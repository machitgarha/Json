<?php

namespace MAChitgarha\Json\Components;

use MAChitgarha\Json\Exceptions\InvalidArgumentException;
use MAChitgarha\Json\Options\InitOption;

/**
 * Data class, providing both encoded and decoded form of data.
 *
 * @todo (This is not a real todo, but only a review request before we hit 2.0.0)
 * The $encoded and $decoded properties are public. This should not be a problem, as Json
 * class has it as a non-public property, and it's not visible to the user, and also,
 * should not be returned to the user.
 * The reason these properties are public is, reducing the overhead of copying things too
 * much around. If we have a method for returning them and for setting them, then if the
 * data is big, then guess what happens. Even returning by reference is not the solution,
 * as it's a silent change and there is no point of having those methods (i.e. it does not
 * differ from properties being public).
 */
class Data
{
    /**
     * Default value for options parameter passed to Data::__construct().
     * @see self::__construct()
     * @var int
     */
    protected const DEFAULT_OPTIONS = 0;

    /**
     * The encoded form of data.
     * @var string
     */
    public $encoded = null;

    /**
     * The decoded form of data. Non-scalar data (and their sub-data) could be either an
     * array or (standard) object.
     * @var mixed
     */
    public $decoded = null;

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
