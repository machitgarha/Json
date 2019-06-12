<?php
/**
 * JSON class file.
 *
 * @author Mohammad Amin Chitgarha <machitgarha@outlook.com>
 * @see https://github.com/MAChitgarha/JSON
 * @see https://packagist.org/packages/machitgarha/json
 */

namespace MAChitgarha\Component;

/**
 * Handles JSON data type.
 *
 * Gets a JSON string or a PHP native array or object and handles it as a JSON data.
 *
 * @see https://github.com/MAChitgarha/JSON/wiki
 * @see https://github.com/MAChitgarha/JSON/wiki/Glossary
 * @todo Import all methods from \ArrayObject.
 * @todo {@see https://stackoverflow.com/questions/29308898/how-do-i-extract-data-from-json-with-php}
 */
class JSON implements \ArrayAccess
{
    /** @var array Holds JSON data as a complete native PHP array (to be handled more easily). */
    protected $data;

    /** @var integer Default data type. */
    protected $defaultDataType = null;

    // Data types
    /** @var int The data without any type changes */
    const TYPE_DEFAULT = 0;
    /** @var int The data in the format of JSON string */
    const TYPE_JSON = 1;
    /** @var int The data as an object */
    const TYPE_OBJECT = 2;
    /** @var int The data as an array */
    const TYPE_ARRAY = 3;
    /** @var int The data as a complete object, without even indexed arrays */
    const TYPE_FULL_OBJECT = 4;

    /**
     * Prepares JSON data.
     *
     * @param string|array|object $data The data; can be either a JSON string, array or object.
     * A JSON string must be a valid JSON object or array, and must not be a boolean, for example.
     * It should not contain any closures, otherwise, they will be considered as empty objects.
     * @throws \InvalidArgumentException When data is not a valid JSON (as described), an array or
     * an object.
     */
    public function __construct($data = [])
    {
        /*
         * Everything must be converted to a complete array, even the in-depth sub-elements. This
         * makes life a lot easier!
         */

        if (($isObject = is_object($data)) || is_array($data)) {
            $this->defaultDataType = $isObject ? self::TYPE_OBJECT : self::TYPE_ARRAY;
            $this->data = self::convertToArray($data);
            return;
        }

        if (is_string($data)) {
            $this->defaultDataType = self::TYPE_JSON;
            // This also checks for any JSON string errors
            $this->data = self::convertJsonToArray($data);
            return;
        }

        // If data is invalid (i.e. is not a countable one)
        throw new \InvalidArgumentException("Wrong data type");
    }

    /**
     * Converts a JSON string to an array.
     *
     * @param string $data Data as JSON string.
     * @return array
     * @throws \InvalidArgumentException If JSON string does not contain a data that could be
     * converted to an array.
     */
    protected static function convertJsonToArray(string $data): array
    {
        $decodedData = json_decode($data, true);
        if (!is_array($decodedData)) {
            throw new \InvalidArgumentException("Invalid (non-objective) JSON string");
        }
        return $decodedData;
    }

    /**
     * Converts a JSON string to an object.
     *
     * @param string $data Data as JSON string.
     * @return object
     * @throws \InvalidArgumentException If the data cannot be converted to an object.
     */
    protected static function convertJsonToObject(string $data): object
    {
        $decodedData = json_decode($data);
        if (!is_object($decodedData)) {
            throw new \InvalidArgumentException("Non-objective JSON string");
        }
        return $decodedData;
    }

    /**
     * Converts countable data to JSON string.
     *
     * @param mixed $data A countable data, either an array or an object.
     * @return string
     * @throws \InvalidArgumentException If data is not countable.
     */
    protected static function convertToJson($data): string
    {
        if (!(is_array($data) || is_object($data))) {
            throw new \InvalidArgumentException("Data must be countable (i.e. array or object)");
        }
        return json_encode($data);
    }

    /**
     * Converts an object to an array completely.
     * @param array|object $data Data as an array or an object.
     * @return array
     * @throws \InvalidArgumentException If data is not countable.
     */
    protected static function convertToArray($data): array
    {
        if (!(is_object($data) || is_array($data))) {
            throw new \InvalidArgumentException("Data must be either an array or an object");
        }

        return json_decode(json_encode($data), true);
    }

    /**
     * Converts an array or an object to an object recursively.
     *
     * @param array $data Data as array or object.
     * @param boolean $forceObject Whether to convert indexed arrays to objects or not.
     * @return object
     * @throws \InvalidArgumentException If data is not countable.
     */
    protected static function convertToObject($data, bool $forceObject = false): object
    {
        if (!(is_object($data) || is_array($data))) {
            throw new \InvalidArgumentException("Data must be either an array or an object");
        }

        return json_decode(json_encode($data, $forceObject ? JSON_FORCE_OBJECT : 0));
    }

    /**
     * Get the desirable value to be used elsewhere.
     * It will convert all countable values to full-indexed arrays. All other values than countable
     * values would be returned exactly the same. 
     *
     * @param mixed $value
     * @return mixed
     */
    protected function getOptimalValue($value)
    {
        if (is_array($value) || is_object($value))
            return self::convertToArray($value);
        
        return $value;
    }

    /**
     * Returns data as the determined type.
     *
     * @param int $type Return type. Can be any of the JSON::TYPE_* constants.
     * @return string|array|object
     * @throws \InvalidArgumentException If the requested type is unknown.
     *
     * @since 0.3.1 Returns JSON if the passed data in constructor was a JSON string.
     */
    public function getData(int $type = self::TYPE_DEFAULT)
    {
        if ($type === self::TYPE_DEFAULT) {
            $type = $this->defaultDataType;
        }

        switch ($type) {
            case self::TYPE_JSON:
                return $this->getDataAsJson();
            case self::TYPE_ARRAY:
                return $this->getDataAsArray();
            case self::TYPE_OBJECT:
                return $this->getDataAsObject();
            case self::TYPE_FULL_OBJECT:
                return $this->getDataAsFullObject();
            default:
                throw new \InvalidArgumentException("Unknown data type");
        }
    }

    /**
     * Returns data as a JSON string.
     *
     * @param int $options The options, like JSON_PRETTY_PRINT. {@link
     * http://php.net/json.constants}
     * @return string
     */
    public function getDataAsJson(int $options = 0): string
    {
        return json_encode($this->data, $options);
    }

    /**
     * Returns data as an array.
     *
     * @return array The data as an array.
     */
    public function getDataAsArray(): array
    {
        return $this->data;
    }

    /**
     * Returns data as an object.
     *
     * @return object The data as an object.
     */
    public function getDataAsObject(): object
    {
        return self::convertToObject($this->data);
    }

    /**
     * Returns data as a full-converted object (i.e. even converts indexed arrays to objects).
     *
     * @return object
     */
    public function getDataAsFullObject(): object
    {
        return self::convertToObject($this->data, true);
    }

    /**
     * Gets a key from a data based on the data type.
     *
     * @param string $key The key.
     * @param array|object $data The data to search in.
     * @return mixed Return the value of the key in data, and if the data is not countable or the
     * key does not exists, return null.
     */
    protected function getKey(string $key, $data)
    {
        if (is_array($data)) {
            return $data[$key] ?? null;
        }
        if (is_object($data)) {
            return $data->$key ?? null;
        }
        // If data is neither array nor object
        return null;
    }

    /**
     * Returns a key by reference.
     * Returns the value of a key in a data by reference. If the key does not exist, sets it to
     * null before returning.
     *
     * @param string $key The key.
     * @param array $data The data to search in.
     * @return mixed The value of the key in the data by reference.
     * @throws \InvalidArgumentException When data is not countable.
     */
    protected function &getKeyByReference(string $key, array &$data)
    {
        if (!isset($data[$key])) {
            $data[$key] = null;
        }
        return $data[$key];
    }

    /**
     * Gets the value of keys in a data recursively.
     *
     * @param array $keys The keys.
     * @param mixed $data The data to crawl keys in it.
     * @return mixed The found value of keys. Returns null if one of keys cannot be found.
     */
    protected function getKeysRecursive(array $keys, $data)
    {
        $keysCount = count($keys);
        // The end of the recursion, crawling keys has finished
        if ($keysCount === 0) {
            return $data;
        }
        // Crawl keys recursively
        else {
            if (!is_array($data)) {
                return null;
            }

            // Get the current key, and remove it from keys array
            $currentKey = array_shift($keys);
            return $this->getKeysRecursive($keys, $this->data[$currentKey] ?? null);
        }
    }

    /**
     * Sets the value of keys in a data recursively.
     *
     * @param array $keys The keys.
     * @param mixed $value The value to set.
     * @param mixed $data The data to crawl keys in it.
     * @return self
     */
    protected function setKeysRecursive(
        array $keys,
        $value,
        &$data
    ): self {
        // Get the current key, and remove it from keys array
        $currentKey = array_shift($keys);
        // Reached the last key, so, setting the value
        if (count($keys) === 0) {
            $data[$currentKey] = $value;
        // Recurs on remained keys
        } else {
            /*
             * If the current key does not exist, set it to an empty countable element based on
             * indexing type. After making sure that the key exists, change the reference of the
             * data to the data key, for next recursion.
             */
            if ($this->getKey($currentKey, $data) === null) {
                $data[$currentKey] = [];
            }

            $data = &$this->getKeyByReference($currentKey, $data);
            $this->setKeysRecursive($keys, $value, $data);
        }

        return $this;
    }

    /**
     * Extract keys from an index into an array by the delimiter.
     *
     * @param string $index The index.
     * @param string $delimiter The delimiter.
     * @return array The extracted keys.
     *
     * @since 0.3.2 Add escaping delimiters, i.e., using delimiters as the part of keys by escaping
     * them using a backslash.
     */
    protected function extractKeysFromIndex(string $index, string $delimiter = "."): array
    {
        if ($index === "") {
            return [""];
        }

        $replacement = "¬";
        $escapedDelimiter = "\\$delimiter";

        // Replace the escaped delimiter with a less-using character
        $index = str_replace($escapedDelimiter, $replacement, $index);

        // Explode index parts by $delimiter
        $keys = explode($delimiter, $index);

        // Set the escaped delimiters
        foreach ($keys as &$key) {
            $key = str_replace($replacement, $delimiter, $key);
        }

        return $keys;
    }

    /**
     * Gets the value of an index in the data.
     *
     * @param string $index The index.
     * @return mixed The value of the index. Returns null if the index not found.
     */
    public function get(string $index)
    {
        return $this->getKeysRecursive($this->extractKeysFromIndex($index), $this->data);
    }

    /**
     * Sets the value to an index in the data.
     *
     * @param string $index The index.
     * @param mixed $value The value to be set.
     * @param bool $extractJson Does value contains a countable JSON string, and you want to
     * extract it to a regular value (i.e. treat it as an array)? Then using this option will be so
     * useful!
     * @return self
     */
    public function set(string $index, $value, bool $extractJson): self
    {
        $delimitedIndex = $this->extractKeysFromIndex($index);

        if ($extractJson && is_string($value)) {
            $value = self::convertJsonToArray($value);
        }

        $this->setKeysRecursive($delimitedIndex, $value, $this->data);
        return $this;
    }

    /**
     * Unset an index in the data.
     *
     * @param string $index The index
     * @return self
     */
    public function unset(string $index): self
    {
        $this->set($index, null);

        // Remove null value from the data
        $this->data = array_filter($this->data, function ($val) {
            return $val !== null;
        });

        return $this;
    }

    /**
     * Determines if an index exists or not.
     *
     * @param string $index The index.
     * @return bool Whether the index is set or not. A null value will be considered as not set.
     */
    public function isSet(string $index): bool
    {
        return $this->get($index) !== null;
    }
    
    /**
     * Iterates over an element.
     *
     * @param ?string $index The index.
     * @param int $returnType Specifies the value type in each iteration if the value is
     * countable. This can be of the JSON::TYPE_* constants. This way, you will ensure
     * @return \Generator
     * @throws \Exception If the value of the data index is not iterable (i.e. neither an array nor
     * an object).
     */
    public function iterate(string $index = null, int $returnType = self::TYPE_DEFAULT): \Generator
    {
        // Get the value of the index in data
        if (($data = $this->getCountable($index)) === null) {
            throw new \Exception("The index is not iterable");
        }

        if ($returnType === self::TYPE_DEFAULT) {
            $returnType = $this->defaultDataType;
        }

        // Define getValue function based on return type
        switch ($returnType) {
            case self::TYPE_JSON:
                $getValue = function ($val) {
                    return new self($val);
                };
                break;

            case self::TYPE_ARRAY:
                $getValue = function ($val) {
                    return $val;
                };
                break;

            case self::TYPE_OBJECT:
                $getValue = function ($val) {
                    return self::convertToObject($val);
                };
                break;

            case self::TYPE_FULL_OBJECT:
                $getValue = function ($val) {
                    return self::convertToObject($val, true);
                };
                break;
            
            default:
                throw new \Exception("Unknown return type");
        }

        foreach ((array)($data) as $key => $val) {
            if (is_array($val)) {
                yield $key => $getValue($val);
            } else {
                yield $key => $val;
            }
        }
    }

    /**
     * Gets the JSON data string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->getDataAsJson();
    }

    /**
     * Gets an element value, if it is countable.
     *
     * @param ?string $index The index. Pass null if you want to get the data itself.
     * @return array|null If the index is countable, returns it; otherwise, returns null.
     */
    protected function getCountable(string $index = null)
    {
        // Get the data
        if ($index === null) {
            return $this->data;
        }

        $value = $this->get($index);
        if (is_array($value)) {
            return $value;
        }
        return null;
    }

    /**
     * Determines whether an element is countable or not.
     *
     * @param string $index The index.
     * @return bool Is the index countable or not.
     */
    public function isCountable(string $index): bool
    {
        return $this->getCountable($index) !== null;
    }

    /**
     * Counts all elements in a countable element.
     *
     * @param ?string $index The index. Pass null if you want to get number of elements in the data.
     * @return int The elements number of the index.
     * @throws \Exception If the element is not countable.
     */
    public function count(string $index = null): int
    {
        // Get the number of keys in the specified index
        $countableValue = $this->getCountable($index);
        if ($countableValue === null) {
            throw new \Exception("The index is not countable");
        }
        return count($countableValue);
    }

    /**
     * Replaces data with a new data.
     *
     * @param array|object|string $data The new data to be replaced.
     * @return self
     */
    public function exchange($data): self
    {
        $this->__construct($data);
        return $this;
    }

    public function offsetExists($index): bool
    {
        return $this->isSet((string)($index));
    }

    public function offsetGet($index)
    {
        return $this->get((string)($index));
    }

    public function offsetSet($index, $value)
    {
        $this->set((string)($index), $value);
    }

    public function offsetUnset($index)
    {
        $this->unset((string)($index));
    }

    /**
     * Pushes a value to the end of a countable element in data.
     *
     * @param mixed $value The value to be inserted.
     * @param ?string $index The index of the countable element to be pushed into. Pass null if you
     * want to push to the data root.
     * @return self
     * @throws \Exception If the index is not countable (i.e. cannot push into it).
     */
    public function push($value, string $index = null): self
    {
        if ($index === null) {
            array_push($this->data, $value);
        } else {
            if (($arrayValue = $this->getCountable($index)) === null) {
                throw new \Exception("The index is not countable");
            }

            array_push($arrayValue, $value);

            $this->set($index, $arrayValue);
        }

        return $this;
    }

    /**
     * Pops the last value of a countable element from data.
     *
     * @param ?string $index The index of the countable element to be popped from. Pass null if you
     * want to pop from the data root.
     * @return self
     * @throws \Exception If the index is not countable (i.e. cannot pop from it).
     */
    public function pop(string $index = null): self
    {
        if ($index === null) {
            array_push($this->data);
        } else {
            if (($arrayValue = $this->getCountable($index)) === null) {
                throw new \Exception("The index is not countable");
            }

            array_pop($arrayValue);

            $this->set($index, $arrayValue);
        }

        return $this;
    }
}
