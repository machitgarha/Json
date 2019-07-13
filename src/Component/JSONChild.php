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
    /** @var JSON  */
    protected $rootData;

    /**
     * @param array|int|string|float|bool|null $data An element of the parent data by-reference.
     * For sure, it is an array or a scalar.
     * @param array $classProperties Properties from the caller class to be set inside this class.
     */
    public function __construct(&$dataPointer, array $properties, JSON $rootData)
    {
        foreach ($properties as $propName => $value) {
            $this->$propName = $value;
        }

        $this->data = &$dataPointer;
        $this->rootData = $rootData;

        if (self::isScalar($dataPointer)) {
            $this->isDataScalar = true;
        }
    }

    /**
     * Switch to data root (i.e. resets the pointer to data root).
     *
     * @return JSON
     */
    public function root(): JSON
    {
        return $this->rootData;
    }
}
