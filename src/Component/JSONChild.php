<?php
/**
 * JSONChild class file.
 *
 * @author Mohammad Amin Chitgarha <machitgarha@outlook.com>
 * @see https://github.com/MAChitgarha/JSON
 * @see https://packagist.org/packages/machitgarha/json
 */

namespace MAChitgarha\Component;

/**
 * Holds an element of the parent data by-reference and make operations on it.
 *
 * @see https://github.com/MAChitgarha/JSON/wiki
 */
class JSONChild extends JSON
{
    /**
     * @param array $data A countable element of the parent data, by-reference.
     * @param array $classProperties Properties from the caller class to be set inside this class.
     */
    public function __construct(&$dataPointer, array $properties)
    {
        foreach ($properties as $propName => $value) {
            $this->$propName = $value;
        }

        $this->data = &$dataPointer;
        if (!is_array($dataPointer)) {
            $this->isDataScalar = true;
        }
    }
}
