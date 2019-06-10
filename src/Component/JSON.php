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
 * @todo Add a new static JSON::convertToJson() method.
 * @todo {@see https://stackoverflow.com/questions/29308898/how-do-i-extract-data-from-json-with-php}
 */
class JSON implements \ArrayAccess
{
    /** @var array|object Holds JSON data as a native PHP data (either object or array). */
    protected $data;

    /** @var bool Is the data passed to the constructor a JSON string? */
    protected $isDefaultDataJson = false;

    // Data types
    /** @var int The data without any type changes */
    const TYPE_DEFAULT = 0;
    /** @var int The data in the format of JSON string */
    const TYPE_JSON = 1;
    /** @var int The data as an object */
    const TYPE_OBJECT = 2;
    /** @var int The data as an array */
    const TYPE_ARRAY = 3;
    /** @var int In crawling keys, a key must be existed, otherwise, an exception will be thrown. */
    const STRICT_INDEXING = 4;

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
        // Convert data to an array
        if (is_string($data)) {
            $this->isDefaultDataJson = true;
            $data = json_decode($data);
        }

        // Force data to be either an array or an object
        if (!(is_array($data) || is_object($data))) {
            throw new \InvalidArgumentException("Wrong data type");
        }

        $this->data = $data;
    }

    /**
     * Returns data as the determined type.
     *
     * @param int $type The type to return. Can be any of the JSON::TYPE_* constants.
     * @param bool $recursive Force $type as the type for all sub-values. No effects when the $type
     * is TYPE_DEFAULT or TYPE_JSON.
     * @return string|array|object
     * @throws \InvalidArgumentException If the requested type is unknown.
     *
     * @since 0.3.1 Returns JSON if the passed data in constructor was a JSON string.
     */
    public function getData(int $type = JSON::TYPE_DEFAULT, bool $recursive = true)
    {
        switch ($type) {
            case self::TYPE_DEFAULT:
                if ($this->isDefaultDataJson) {
                    return $this->getDataAsJson();
                }
                return $this->data;
            case self::TYPE_JSON:
                return $this->getDataAsJson();
            case self::TYPE_ARRAY:
                return $this->getDataAsArray($recursive);
            case self::TYPE_OBJECT:
                return $this->getDataAsObject($recursive);
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
     * @param bool $recursive Force array as the type for all sub-values.
     * @return array The data as an array.
     */
    public function getDataAsArray(bool $recursive = true): array
    {
        if ($recursive) {
            return json_decode(json_encode($this->data), true);
        } else {
            return (array)($this->data);
        }
    }

    /**
     * Returns data as an object.
     *
     * @param bool $recursive Force object as the type for all sub-values.
     * @return object The data as an object.
     */
    public function getDataAsObject(bool $recursive = true): object
    {
        if ($recursive) {
            return json_decode(json_encode($this->data, JSON_FORCE_OBJECT));
        } else {
            return (object)($this->data);
        }
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
     * Sets a key to a value in a data.
     *
     * @param string $key The key.
     * @param array|object $data The data to be modified.
     * @param mixed $value The value to be set.
     * @return self
     * @throws \InvalidArgumentException If $data is not countable.
     */
    protected function setKey(string $key, &$data, $value)
    {
        if (is_array($data)) {
            $data[$key] = $value;
        } elseif (is_object($data)) {
            $data->$key = $value;
        } else {
            throw new \InvalidArgumentException("Data must be countable");
        }

        return $this;
    }

    /**
     * Returns a key by reference.
     * Returns the value of a key in a data by reference. If the key does not exist, sets it to
     * null before returning.
     *
     * @param string $key The key.
     * @param array|object $data The data to search in.
     * @return mixed The value of the key in the data by reference.
     * @throws \InvalidArgumentException When data is not countable.
     */
    protected function &getKeyByReference(string $key, &$data)
    {
        if (is_array($data)) {
            if (!isset($data[$key])) {
                $data[$key] = null;
            }
            return $data[$key];
        } elseif (is_object($data)) {
            if (!isset($data->$key)) {
                $data->$key = null;
            }
            return $data->$key;
        } else {
            throw new \InvalidArgumentException("Wrong data type");
        }
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
            // Get the current key, and remove it from keys array
            $currentKey = array_shift($keys);
            return $this->getKeysRecursive($keys, $this->getKey($currentKey, $data));
        }
    }

    /**
     * Sets the value of keys in a data recursively.
     *
     * @param array $keys The keys.
     * @param mixed $value The value to set.
     * @param mixed $data The data to crawl keys in it.
     * @param int $indexingType The type of the value to set when reaching an undefined key.
     * Possible values are: TYPE_ARRAY, TYPE_OBJECT, STRICT_INDEXING
     * @return self
     * @throws \InvalidArgumentException If the $indexingType is wrong.
     * @throws \Exception If any of the keys is not existed, when $indexingType is STRICT_INDEXING.
     */
    protected function setKeysRecursive(
        array $keys,
        $value,
        &$data,
        int $indexingType = JSON::TYPE_ARRAY
    ): self {
        // Validate indexing type
        if (!in_array($indexingType, [
            self::TYPE_ARRAY,
            self::TYPE_OBJECT,
            self::STRICT_INDEXING
        ])) {
            throw new \InvalidArgumentException("Wrong indexing type");
        }

        // Get the current key, and remove it from keys array
        $currentKey = array_shift($keys);
        // Reached the last key, so, setting the value
        if (count($keys) === 0) {
            $this->setKey($currentKey, $data, $value);
            return $this;
        // Recurs on remained keys
        } else {
            /*
             * If the current key does not exist, set it to an empty countable element based on
             * indexing type. After making sure that the key exists, change the reference of the
             * data to the data key, for next recursion.
             */
            if ($this->getKey($currentKey, $data) === null) {
                switch ($indexingType) {
                    case self::TYPE_ARRAY:
                        $this->setKey($currentKey, $data, array());
                        break;
                    case self::TYPE_OBJECT:
                        $this->setKey($currentKey, $data, new \stdClass());
                        break;
                    case self::STRICT_INDEXING:
                        throw new \Exception("Key '$currentKey' is not defined");
                    // Default case is checked at first
                }
            }

            $data = &$this->getKeyByReference($currentKey, $data);
            $this->setKeysRecursive($keys, $value, $data, $indexingType);
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
     * @param int $indexingType The type of the value to set when reaching an undefined key. It can
     * be either an array (TYPE_ARRAY) or an object (TYPE_OBJECT), or TYPE_STRICT if you want to
     * get exceptions when reaching an undefined key; i.e. all keys, except the last one, must
     * exist.
     * @return self
     */
    public function set(string $index, $value, int $indexingType = JSON::TYPE_ARRAY): self
    {
        $delimitedIndex = $this->extractKeysFromIndex($index);
        $this->setKeysRecursive($delimitedIndex, $value, $this->data, $indexingType);
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
     * @param integer $returnType Specifies the value type in each iteration if the value is
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

        // Define getValue function based on return type
        switch ($returnType) {
            case self::TYPE_DEFAULT:
                $getValue = function ($val) {
                    return $val;
                };
                break;
            
            case self::TYPE_ARRAY:
                $getValue = function ($val) {
                    return (array)($val);
                };
                break;

            case self::TYPE_OBJECT:
                $getValue = function ($val) {
                    return (object)($val);
                };
                break;
            
            case self::TYPE_JSON:
                $getValue = function ($val) {
                    return new self($val);
                };
                break;
            
            default:
                throw new \Exception("Unknown return type");
        }

        foreach ((array)($data) as $key => $val) {
            // Check if it is countable
            if (is_array($val) || is_object($val))
                yield $key => $getValue($val);
            else
                yield $key => $val;
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
     * @param ?string $index The index. Pass null if you want to get number of elements in the data.
     * @return mixed If the index is countable, returns it; otherwise, returns null.
     */
    protected function getCountable(string $index = null)
    {
        // Get the data
        if ($index === null) {
            return $this->data;
        }

        $value = $this->get($index);
        if (is_object($value) || is_array($value)) {
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
        return count((array)($countableValue));
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
        return $this->isSet($index);
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
     * Pushes a value to the end of the data.
     *
     * @param mixed $value The value to be inserted.
     * @return self
     */
    public function push($value): self
    {
        $data = $this->getDataAsArray();
        array_push($data, $value);

        settype($data, gettype($this->data));
        $this->data = $data;

        return $this;
    }

    /**
     * Pops the last value of the array.
     *
     * @return self
     */
    public function pop(): self
    {
        $data = $this->getDataAsArray();
        array_pop($data);

        settype($data, gettype($this->data));
        $this->data = $data;

        return $this;
    }
}
