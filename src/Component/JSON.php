<?php
/**
 * JSON class file.
 *
 * @author Mohammad Amin Chitgarha <machitgarha@outlook.com>
 * @see https://github.com/MAChitgarha/JSON
 * @see https://packagist.org/packages/machitgarha/json
 */

namespace MAChitgarha\Component;

use MAChitgarha\Exception\JSON\Exception;
use MAChitgarha\Exception\JSON\InvalidArgumentException;
use MAChitgarha\Exception\JSON\InvalidJsonException;
use MAChitgarha\Exception\JSON\UncountableJsonException;
use MAChitgarha\Exception\JSON\UncountableValueException;
use MAChitgarha\Exception\JSON\ScalarDataException;

/**
 * Handles JSON data type.
 *
 * Gets a JSON string or a PHP native array or object and handles it as a JSON data.
 *
 * @see https://github.com/MAChitgarha/JSON/wiki
 * @see https://github.com/MAChitgarha/JSON/wiki/Glossary
 * @todo Import all methods from \ArrayObject.
 * @todo {@see https://stackoverflow.com/questions/29308898/how-do-i-extract-data-from-json-with-php}
 * @todo Add toArray() method for scalar types.
 * @todo Add clear() method to clear an array.
 * @todo Change default data type when changing data.
 */
class JSON implements \ArrayAccess
{
    /**
     * @var array|int|string|float|bool|null Holds JSON data as a complete native PHP array (to be
     * handled more easily). If the data passed to the constructor is countable, then the data will
     * be saved as an array; otherwise, if it's scalar, it will be saved exactly as it is. Also,
     * these allowed types can be in a string that contains a valid JSON data.
     */
    protected $data;

    /**
     * @var int Default data type. Possible values: TYPE_JSON_STRING, TYPE_ARRAY, TYPE_OBJECT,
     * TYPE_SCALAR (class constants).
     */
    protected $defaultDataType = null;

    /** @var int Options passed to the constructor. */
    protected $options = 0;

    /** @var bool {@see self::OPT_JSON_DECODE_ALWAYS} */
    protected $jsonDecodeAlways = false;

    /** @var bool {@see self::OPT_PRINT_SCALAR_AS_IS} */
    protected $printScalarAsIs = false;

    // Data types
    /** @var int The data type which you passed at creating new instance (i.e. constructor). */
    const TYPE_DEFAULT = 0;
    /** @var int JSON string data type. */
    const TYPE_JSON_STRING = 1;
    /** @var int Object data type (recursive), without converting indexed arrays to objects. */
    const TYPE_OBJECT = 2;
    /** @var int Array data type (recursive). */
    const TYPE_ARRAY = 3;
    /** @var int Object data type (recursive), with converting even indexed arrays to objects. */
    const TYPE_FULL_OBJECT = 4;
    /** @var int JSON class data type, i.e. a new instance of the class itself. */
    const TYPE_JSON_CLASS = 5;
    /** @var int Scalar data type, either an integer, a string, a float, a boolean or NULL. */
    const TYPE_SCALAR = 6;

    // Options
    /**
     * @var int To decode every valid JSON string when setting values or not. This would be so much
     * useful if you're working with JSON strings a lot. The first thing that this option does is
     * that checks if a JSON string is a valid one and contains an object; and if it is, then
     * extract it to an array and make further operations, like setting the new value.
     */
    const OPT_JSON_DECODE_ALWAYS = 1;
    /**
     * @var int Consider data passed into the constructor as string, even if it's a valid JSON data;
     * in other words, don't decode it. This option has effect only in the constructor, and no other
     * methods will be affected by this. It won't work if you use it in combination with
     * JSON::OPT_TREAT_AS_JSON_STRING.
     */
    const OPT_TREAT_AS_STRING = 2;
    /**
     * @var int Consider data passed into the constructor as a JSON string. Using this option leads
     * to exceptions if the JSON string is not valid. This option has effect only in the
     * constructor, and no other methods will be affected by this.
     */
    const OPT_TREAT_AS_JSON_STRING = 8;
    /**
     * @var int When using 'echo' or like to print JSON class, i.e. when calling JSON::__toString(),
     * print all scalar data as is, without encoding it as a JSON string.
     */
    const OPT_PRINT_SCALAR_AS_IS = 4;

    /**
     * Prepares JSON data.
     *
     * @param mixed $data The data; can be either countable (i.e. a valid JSON string, array or
     * object) or scalar. Data should not contain any closures; otherwise, they will be considered
     * as empty objects.
     * @param int $options The additional options. Can be one of the JSON::OPT_* constants.
     * @throws InvalidArgumentException Using one of the JSON::OPT_TREAT_AS_STRING or
     * JSON::OPT_TREAT_AS_JSON_STRING options and passing data as non-string.
     * @throws InvalidArgumentException If data is neither countable nor scalar.
     * @throws InvalidJsonException If JSON::OPT_TREAT_AS_JSON_STRING is enabled and data is not a
     * valid JSON.
     */
    public function __construct($data = [], int $options = 0)
    {
        $this->options = $options;

        // Set global options
        $this->jsonDecodeAlways = (bool)($options & self::OPT_JSON_DECODE_ALWAYS);
        $this->printScalarAsIs = (bool)($options & self::OPT_PRINT_SCALAR_AS_IS);

        $treatAsJsonString = (bool)($options & self::OPT_TREAT_AS_JSON_STRING);
        $treatAsString = (bool)($options & self::OPT_TREAT_AS_STRING);

        // Check data type
        $isString = is_string($data);

        if (($treatAsJsonString || $treatAsString) && !$isString) {
            throw new InvalidArgumentException("You must pass data as string when you use one of "
                . "the JSON::OPT_TREAT_AS_STRING or JSON::OPT_TREAT_AS_JSON_STRING options");
        }

        if ($type = self::isArrayOrObject($data)) {
            $this->defaultDataType = $type;
            $this->setDataTo(self::convertToArray($data));
            return;
        }

        if (($treatAsJsonString || !$treatAsString) && $isString) {
            list($isJsonValid, $decodedData) = $this->validateStringAsJson($data, true);

            if (!$isJsonValid && $treatAsJsonString) {
                throw new InvalidJsonException();
            }
            
            if ($isJsonValid) {
                $this->defaultDataType = self::TYPE_JSON_STRING;
                if (is_scalar($decodedData) || $decodedData === null) {
                    $this->defaultDataType = self::TYPE_SCALAR;
                }

                $this->setDataTo($decodedData);
                return;
            }
        }

        /*
         * The code will NOT reach here, if, JSON::OPT_TREAT_AS_JSON_STRING is enabled. So, the
         * code will reach here only if one of the following things happen:
         * 1. JSON::OPT_TREAT_AS_STRING is enabled.
         * 2. JSON::OPT_TREAT_AS_STRING is not enabled, and data is not a valid JSON string.
         */
        if (is_scalar($data) || $data === null) {
            $this->defaultDataType = self::TYPE_SCALAR;
            $this->setDataTo($data);
            return;
        }

        // If data is invalid
        throw new InvalidArgumentException("Data must be either countable or scalar");
    }

    /**
     * Sets data to an array, a scalar data or null.
     * The recommended way to change JSON::$data property is using this method, as it does prevents
     * from an invalid data to be replaced in JSON::$data.
     *
     * @param array|int|string|float|bool|null $data The data to be replaced in JSON::$data.
     * @return void
     * @throws InvalidArgumentException Any data that is not array or scalar is invalid.
     */
    protected function setDataTo($data)
    {
        if (!(is_array($data) || self::isScalar($data))) {
            throw new InvalidArgumentException("Invalid data type");
        }
        $this->data = $data;
    }

    /**
     * Checks a string data to be a valid JSON string.
     *
     * @param string $data Data to be validated.
     * @param bool $assoc Return decoded JSON as associative array or not.
     * @return array An array of two values:
     * [0]: Is the string a valid JSON or not,
     * [1]: The decoded JSON string, and it will be null if the string is not a valid JSON.
     */
    protected static function validateStringAsJson(string $data, bool $assoc = true): array
    {
        $decodedData = json_decode($data, $assoc);
        if (json_last_error() === JSON_ERROR_NONE) {
            return [true, $decodedData];
        }
        return [false, $decodedData];
    }

    /**
     * Checks if a string is a valid JSON or not.
     *
     * @param string $data Data to be checked.
     * @return bool
     */
    public static function isValidJson(string $data): bool
    {
        return self::validateStringAsJson($data)[0];
    }

    /**
     * Reads a valid JSON string, and if it is invalid, throws an exception.
     *
     * @param string $data String data to be read.
     * @return mixed A non-null value.
     * @throws InvalidJsonException When data is an invalid JSON string.
     */
    public static function readValidJson(string $data)
    {
        list($isValidJson, $decodedJson) = self::validateStringAsJson($data);
        if (!$isValidJson) {
            throw new InvalidJsonException();
        }
        return $decodedJson;
    }

    /**
     * Tells if data is an array or data to be either an array or object
     *
     * @param mixed $data The data.
     * @param bool $throwException To throw exceptions when data is not either an array or an
     * object or not.
     * @return int 0 if is not any of them, JSON::TYPE_ARRAY if it is an array and JSON::TYPE_OBJECT
     * if it is an object.
     * @throws InvalidArgumentException When $throwException is set to true and data is not either
     * an array or an object.
     */
    protected static function isArrayOrObject($data, bool $throwException = false): int
    {
        $isArray = is_array($data);
        $isObject = is_object($data);

        if (!($isArray || $isObject) && $throwException) {
            throw new InvalidArgumentException("Data must be either an array or an object");
        }

        return ($isArray ? self::TYPE_ARRAY :
            ($isObject ? self::TYPE_OBJECT : 0));
    }

    /**
     * Tells whether data type is scalar or not.
     * A scalar type, can be an integer, a string, a float, a boolean, or a null value.
     *
     * @param mixed $data The data.
     * @param bool $throwException Throw exceptions when data is not scalar?
     * @return bool
     * @throws InvalidArgumentException When $throwException is set to true and data is not scalar.
     */
    protected static function isScalar($data, bool $throwException = false): bool
    {
        $isScalar = is_scalar($data) || $data === null;

        if (!$isScalar && $throwException) {
            throw new InvalidArgumentException("Data must be scalar");
        }

        return $isScalar;
    }

    /**
     * Tells whether JSON::$data is scalar or not.
     *
     * @return bool
     */
    protected function isDataScalar()
    {
        return self::isScalar($this->data);
    }

    /**
     * Converts a JSON string to an array.
     *
     * @param string $data Data as JSON string.
     * @return array
     * @throws UncountableJsonException If JSON string does not contain a data that could be
     * converted to an array.
     */
    protected static function convertJsonToArray(string $data): array
    {
        $decodedData = json_decode($data, true);
        if (!is_array($decodedData)) {
            throw new UncountableJsonException();
        }
        return $decodedData;
    }

    /**
     * Converts a JSON string to an object.
     *
     * @param string $data Data as JSON string.
     * @return object
     * @throws UncountableJsonException If the data cannot be converted to an object.
     */
    protected static function convertJsonToObject(string $data): object
    {
        $decodedData = json_decode($data);
        if (!is_object($decodedData)) {
            throw new UncountableJsonException();
        }
        return $decodedData;
    }

    /**
     * Converts countable data to JSON string.
     *
     * @param array|object $data A countable data, either an array or an object.
     * @return string
     */
    protected static function convertCountableToJson($data): string
    {
        self::isArrayOrObject($data, true);
        return json_encode($data);
    }

    /**
     * Converts an object to an array completely.
     *
     * @param array|object $data Data as an array or an object.
     * @return array
     */
    protected static function convertToArray($data): array
    {
        self::isArrayOrObject($data, true);
        return json_decode(json_encode($data), true);
    }

    /**
     * Converts an array or an object to an object recursively.
     *
     * @param array $data Data as array or object.
     * @param bool $forceObject Whether to convert indexed arrays to objects or not. It may be
     * obvious, but data root will always be an object, even with numeric indexes.
     * @return object
     */
    protected static function convertToObject($data, bool $forceObject = false): object
    {
        self::isArrayOrObject($data, true);
        return (object)(json_decode(json_encode($data, $forceObject ? JSON_FORCE_OBJECT : 0)));
    }

    /**
     * Get the desirable value to be used elsewhere.
     * It will convert all countable values to full-indexed arrays. All other values than countable
     * values would be returned exactly the same.
     * Also, if OPT_JSON_DECODE_ALWAYS option is enabled, then it returns all
     *
     * @param mixed $value
     * @return mixed
     */
    protected function getOptimalValue($value)
    {
        if (self::isArrayOrObject($value)) {
            return self::convertToArray($value);
        }

        // JSON::OPT_JSON_DECODE_ALWAYS handler
        if ($this->jsonDecodeAlways && is_string($value)) {
            // Validating JSON string
            try {
                return self::convertJsonToArray($value);
            } catch (UncountableJsonException $e) {
            }
        }
        
        return $value;
    }

    /**
     * Returns data as the determined type.
     *
     * @param int $type Return type. Can be any of the JSON::TYPE_* constants, except
     * JSON::TYPE_JSON_CLASS and JSON::TYPE_SCALAR.
     * @return string|array|object
     * @throws InvalidArgumentException If $type is invalid.
     *
     * @since 0.3.1 Returns JSON if the passed data in constructor was a JSON string.
     */
    public function getData(int $type = self::TYPE_DEFAULT)
    {
        if ($type === self::TYPE_DEFAULT) {
            $type = $this->defaultDataType;
        }

        switch ($type) {
            case self::TYPE_JSON_STRING:
                return $this->getDataAsJsonString();
            case self::TYPE_ARRAY:
                return $this->getDataAsArray();
            case self::TYPE_OBJECT:
                return $this->getDataAsObject();
            case self::TYPE_FULL_OBJECT:
                return $this->getDataAsFullObject();
            default:
                throw new InvalidArgumentException("Unknown type to be returned");
        }
    }

    /**
     * Returns data as a JSON string.
     *
     * @param int $options The options, like JSON_PRETTY_PRINT. {@link
     * http://php.net/json.constants}
     * @return string
     */
    public function getDataAsJsonString(int $options = 0): string
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
     * Follows keys to do (a) specific operation(s) with the element.
     * Crawl keys recursively, and find the requested element. Then, by using the closure, do a
     * specific set of operations with that element.
     *
     * @param array $keys The keys to be crawled recursively. If you pass it an empty array, then
     * the method won't do anything.
     * @param array $data The data. It must be completely array (including its sub-elements), or
     * you may encounter errors.
     * @param callable $operation A set of operations to do with the element, as a closure. The
     * closure will get two arguments:
     * 1. The parent array of the element,
     * 2. The key (as integer or string) to be accessed to that element using the parent array.
     * The value returned by the closure will also be returned by this method.
     * @param boolean $strictIndexing To create keys as empty arrays and continue recursion if the
     * key cannot be found. For example, you can turn this on when you want to get an element's
     * value and you want to ensure that the element exists.
     * @return mixed Return the return value of the closure ($operation).
     * @throws Exception When strict indexing is enabled but a key does not exist.
     * @throws Exception When a key contains a non-array (i.e. uncountable) data, and thus,
     * cannot code crawling keys cannot be continued.
     */
    protected function crawlKeysRecursive(array $keys, array &$data, callable $operation, bool $strictIndexing = false)
    {
        $keysCount = count($keys);

        // End of recursion
        if ($keysCount === 1) {
            $lastKey = $keys[0];
            if (!array_key_exists($lastKey, $data)) {
                if ($strictIndexing) {
                    throw new Exception("The key '$lastKey' does not exist");
                } else {
                    $data[$lastKey] = null;
                }
            }
            return $operation($data, $lastKey);
        }

        // Crawl keys recursively
        if ($keysCount > 1) {
            // Get the current key, and remove it from keys array
            $curKey = array_shift($keys);

            if (!array_key_exists($curKey, $data)) {
                if ($strictIndexing) {
                    throw new Exception("The key '$curKey' does not exist");
                } else {
                    $data[$curKey] = [];
                }
            } elseif (!is_array($data[$curKey])) {
                throw new UncountableValueException("The key '$curKey' contains uncountable value");
            }

            // Recursion
            return $this->crawlKeysRecursive($keys, $data[$curKey], $operation, $strictIndexing);
        }
    }

    /**
     * Handles empty keys and scalar data for {@link self::crawlKeysRecursive()}.
     * Don't allow scalar types, and throw a special exception when data passed to the method is
     * scalar.
     *
     * @see self::crawlKeysRecursive()
     * @throws ScalarDataException
     * @throws InvalidArgumentException When a non-scalar and non-array data is passed.
     */
    protected function crawlKeys(array $keys, &$data, callable $operation, bool $strictIndexing = false)
    {
        if (self::isScalar($data)) {
            throw new ScalarDataException("Cannot use the function on scalar data");
        }

        if (!is_array($data)) {
            throw new InvalidArgumentException("Non-array data passed");
        }

        if (count($keys) === 0) {
            // Don't merge these two lines, to make passing by reference work
            $data = [$data];
            $returnValue = $operation($data, 0);
            $data = $data[0];
            return $returnValue;
        }
        return $this->crawlKeysRecursive($keys, $data, $operation, $strictIndexing);
    }

    /**
     * Extract keys from an index into an array by the delimiter.
     *
     * @param ?string $index The index.
     * @param string $delimiter The delimiter.
     * @return array The extracted keys.
     *
     * @since 0.3.2 Add escaping delimiters, i.e., using delimiters as the part of keys by escaping
     * them using a backslash.
     */
    protected function extractIndex(string $index = null, string $delimiter = "."): array
    {
        if ($index === null) {
            return [];
        }

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
     * @param ?string $index The index.
     * @return mixed The value of the index. Returns null if the index not found.
     * @throws ScalarDataException If data is scalar and $index is not null.
     */
    public function get(string $index = null)
    {
        if ($this->isDataScalar()) {
            if ($index === null) {
                return $this->data;
            } else {
                throw new ScalarDataException("Indexing is invalid");
            }
        }

        try {
            return $this->crawlKeys($this->extractIndex($index), $this->data, function ($data, $key) {
                return $data[$key];
            }, true);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Sets the value to an index in the data.
     *
     * @param mixed $value The value to be set.
     * @param ?string $index The index. Pass null if data is scalar.
     * @return self
     * @throws ScalarDataException If data is scalar and $index is not null.
     */
    public function set($value, string $index = null): self
    {
        $value = $this->getOptimalValue($value);

        if ($this->isDataScalar()) {
            if ($index === null) {
                $this->setDataTo($value);
                return $this;
            } else {
                throw new ScalarDataException("Indexing is invalid");
            }
        }

        $this->crawlKeys($this->extractIndex($index), $this->data, function (&$data, $key) use ($value) {
            $data[$key] = $value;
        });
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
        $this->crawlKeys($this->extractIndex($index), $this->data, function (&$data, $key) {
            unset($data[$key]);
        }, true);
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
     * countable. Can be of the JSON::TYPE_* constants, except some.
     * @return \Generator
     * @throws UncountableValueException If the element is not iterable (i.e. is not an array).
     * @throws InvalidArgumentException When passing $returnType a wrong type.
     */
    public function iterate(string $index = null, int $returnType = self::TYPE_DEFAULT): \Generator
    {
        // Get the value of the index in data
        if (($data = $this->getCountable($index)) === null) {
            throw new UncountableValueException("'$index' is not iterable");
        }

        if ($returnType === self::TYPE_DEFAULT) {
            $returnType = $this->defaultDataType;
        }

        // Define getValue function based on return type
        switch ($returnType) {
            case self::TYPE_JSON_STRING:
                $getValue = function ($val) {
                    return self::convertCountableToJson($val);
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
                throw new InvalidArgumentException("Unknown return type");
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
     * Gets data as JSON string.
     *
     * @return string
     */
    public function __toString(): string
    {
        if ($this->printScalarAsIs && $this->isDataScalar()) {
            return $this->data;
        }
        return $this->getDataAsJsonString();
    }

    /**
     * Gets an element value, if it is countable.
     *
     * @param ?string $index The index. Pass null if you want to get the data itself.
     * @return array|null If the index is countable, returns it; otherwise, returns null.
     */
    protected function getCountable(string $index = null)
    {
        if ($this->isDataScalar()) {
            throw new ScalarDataException();
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
     * @throws UncountableValueException If the element is not countable.
     */
    public function count(string $index = null): int
    {
        // Get the number of keys in the specified index
        $countableValue = $this->getCountable($index);
        if ($countableValue === null) {
            throw new UncountableValueException("'$index' is not countable");
        }
        return count($countableValue);
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
        $this->set($value, (string)($index));
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
     * @throws UncountableValueException
     */
    public function push($value, string $index = null): self
    {
        $value = $this->getOptimalValue($value);

        try {
            $this->crawlKeys($this->extractIndex($index), $this->data, function (&$data, $key) use ($value) {
                if (!is_array($data)) {
                    throw new UncountableValueException();
                }
                array_push($data[$key], $value);
            }, true);
        } catch (UncountableValueException $e) {
            throw new UncountableValueException("'$index' is not countable");
        }

        return $this;
    }

    /**
     * Pops the last value of a countable element from data.
     *
     * @param ?string $index The index of the countable element to be popped from. Pass null if you
     * want to pop from the data root.
     * @return self
     * @throws UncountableValueException
     */
    public function pop(string $index = null): self
    {
        try {
            $this->crawlKeys($this->extractIndex($index), $this->data, function (&$data, $key) {
                if (!is_array($data)) {
                    throw new UncountableValueException();
                }
                array_pop($data[$key]);
            }, true);
        } catch (UncountableValueException $e) {
            throw new UncountableValueException("'$index' is not countable");
        }

        return $this;
    }
}
