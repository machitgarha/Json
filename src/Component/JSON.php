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
use MAChitgarha\Exception\JSON\UncountableValueException;
use MAChitgarha\Exception\JSON\ScalarDataException;
use MAChitgarha\Exception\JSON\JsonException;

/**
 * Handles JSON data type.
 *
 * Gets a JSON string or a PHP native array or object and handles it as a JSON data.
 *
 * @see https://github.com/MAChitgarha/JSON/wiki
 * @see https://github.com/MAChitgarha/JSON/wiki/Glossary
 */
class JSON implements \ArrayAccess
{
    /**
     * @var array|int|string|float|bool|null JSON data as a complete native PHP array (to be
     * handled more easily), or as a scalar type. It is important to note that what we mean from a
     * scalar type can be NULL, also. If the data passed to the constructor is countable, then the
     * data will be saved as an array; otherwise, if it's scalar, it will be saved as is. Also,
     * these allowed types can be in a string that contains a valid JSON data.
     */
    protected $data;

    /** @var bool A scalar type, can be an integer, a string, a float, a boolean, or NULL. */
    protected $isDataScalar = false;

    /** @var int Default data type; can be one of the JSON::TYPE_* constants. */
    protected $defaultDataType = self::TYPE_JSON_STRING;

    /** @var int {@see self::setReturnType()}. */
    protected $returnType = self::TYPE_DEFAULT;

    /** @var bool {@see self::setReturnType()}. */
    protected $returnScalarAsScalar = true;

    /** @var int Options passed to the constructor. */
    protected $options = 0;

    /** @var bool {@see self::OPT_JSON_DECODE_ALWAYS} */
    protected $jsonDecodeAlways = false;

    /** @var int {@see self::setJsonRecursionDepth()} */
    protected $jsonRecursionDepth = 512;

    // Data types
    /** @var int The data type which you passed at creating new instance (i.e. constructor). */
    const TYPE_DEFAULT = 0;
    /** @var int JSON string data type. */
    const TYPE_JSON_STRING = 1;
    /** @var int Array data type (recursive). */
    const TYPE_ARRAY = 3;
    /** @var int Object data type (recursive), without converting indexed arrays to objects. */
    const TYPE_OBJECT = 2;
    /** @var int Object data type (recursive), with converting even indexed arrays to objects. */
    const TYPE_FULL_OBJECT = 4;

    // Options
    /**
     * @var int Check every string value to be a valid JSON string, and if it is, decode it and use
     * the decoded value instead of the string itself. However, if a string does not contain a valid
     * JSON string, then use the string itself. This option affects performance in some cases, but
     * it would be so much useful if you work with JSON strings a lot.
     */
    const OPT_JSON_DECODE_ALWAYS = 1;
    /**
     * @var int Consider data passed into the constructor as string, even if it's a valid JSON data;
     * in other words, don't decode it. Using this option, every non-string and uncountable values
     * will be converted to a string (i.e. integers, booleans and NULL). This option only affects
     * the constructor. It won't have any effects if you use it in combination with
     * JSON::OPT_TREAT_AS_JSON_STRING.
     */
    const OPT_TREAT_AS_STRING = 2;
    /**
     * @var int Force data passed into the constructor as a JSON string. Using this option leads
     * to exceptions if the JSON string is not valid. This option only affects the constructor. Note
     * that the constructor checks a string data as a JSON string by default.
     */
    const OPT_TREAT_AS_JSON_STRING = 8;

    /**
     * Prepares JSON data.
     *
     * @param mixed $data The data; can be either countable (i.e. a valid JSON string, an array or
     * an object) or scalar. Data should not contain any closures; otherwise, they will be
     * considered as empty objects.
     * @param int $options The additional options. Can be one of the JSON::OPT_* constants.
     * @throws InvalidArgumentException Using JSON::OPT_TREAT_AS_JSON_STRING option and passing
     * a non-string data.
     * @throws InvalidArgumentException If data is neither countable nor scalar.
     * @throws InvalidJsonException If JSON::OPT_TREAT_AS_JSON_STRING is enabled and data is not a
     * valid JSON.
     */
    public function __construct($data = [], int $options = 0)
    {
        $this->options = $options;

        // Set global options
        $this->jsonDecodeAlways = (bool)($options & self::OPT_JSON_DECODE_ALWAYS);

        $treatAsJsonString = (bool)($options & self::OPT_TREAT_AS_JSON_STRING);
        $treatAsString = (bool)($options & self::OPT_TREAT_AS_STRING);

        // Check data type
        $isString = is_string($data);

        if ($treatAsJsonString && !$isString) {
            throw new InvalidArgumentException("You must pass data as string when you use "
                . "JSON::OPT_TREAT_AS_JSON_STRING option");
        }

        if ($type = self::isArrayOrObject($data)) {
            $this->defaultDataType = $type;
            $this->setDataTo($this->convertToArray($data));
            return;
        }

        if (($treatAsJsonString || !$treatAsString) && $isString) {
            list($isJsonValid, $decodedData) = $this->validateStringAsJson($data, true);

            if (!$isJsonValid && $treatAsJsonString) {
                throw new InvalidJsonException();
            }
            
            if ($isJsonValid) {
                $this->defaultDataType = self::TYPE_JSON_STRING;
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
            $this->isDataScalar = true;
            $this->setDataTo($treatAsString ? (string)($data) : $data);
            return;
        }

        // If data is invalid
        throw new InvalidArgumentException("Data must be either countable or scalar");
    }

    /**
     * Sets data to an array or a scalar value.
     * The recommended way to change JSON::$data property is using this method, as it prevents from
     * an invalid data to be replaced in JSON::$data.
     *
     * @param array|int|string|float|bool|null $value The value to be replaced in JSON::$data.
     * @return self
     * @throws InvalidArgumentException Any data that is not array or scalar is invalid.
     */
    protected function setDataTo($value): self
    {
        $isScalar = self::isScalar($value);

        if (!(is_array($value)) && !$isScalar) {
            throw new InvalidArgumentException("Invalid data type");
        }

        $this->isDataScalar = $isScalar;
        $this->data = $value;
        return $this;
    }

    /**
     * Determines if data is an array or an object, or force it to be one of them.
     *
     * @param mixed $data
     * @param bool $throwException To throw exceptions when data is not either an array or an
     * object or not.
     * @return int 0 if is not any of them, JSON::TYPE_ARRAY if it is an array and JSON::TYPE_OBJECT
     * if it is an object.
     * @throws InvalidArgumentException
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
     * A scalar type, can be an integer, a string, a float, a boolean, or NULL.
     *
     * @param mixed $data
     * @param bool $throwException To throw exceptions when data is not scalar or not.
     * @return bool
     * @throws InvalidArgumentException
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
     * Encodes a data as JSON.
     * See json_encode() documentation for more details.
     *
     * @param mixed $data
     * @param int $options
     * @param int $depth
     * @return string
     * @throws JsonException
     */
    public static function encodeToJson($data, int $options = 0, int $depth = 512): string
    {
        $encodedData = json_encode($data, $options, $depth);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new JsonException("Cannot encode JSON string");
        }
        return $encodedData;
    }

    /**
     * Decodes a JSON string.
     * See json_decode() documentation for more details.
     *
     * @param string $data
     * @param bool $assoc
     * @param int $depth
     * @param int $options
     * @return mixed
     * @throws JsonException
     */
    public static function decodeJson(
        string $data,
        bool $assoc = false,
        int $depth = 512,
        int $options = 0
    ) {
        $decodedData = json_decode($data, $assoc, $depth, $options);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new JsonException("Cannot decode JSON string");
        }
        return $decodedData;
    }

    /**
     * Checks a string data to be a valid JSON string.
     *
     * @param string $data
     * @param bool $assoc Return decoded JSON as associative array or not.
     * @return array An array of two values:
     * [0]: Is the string a valid JSON or not,
     * [1]: The decoded JSON string, and it will be null if the string is not a valid JSON, or an
     * error occurred while decoding it.
     */
    protected static function validateStringAsJson(string $data, bool $assoc = false): array
    {
        try {
            $decodedData = self::decodeJson($data, $assoc);
        } catch (JsonException $e) {
            return [false, null];
        }
        return [true, $decodedData];
    }

    /**
     * Checks if a string is a valid JSON or not.
     *
     * @param string $data Data to be checked.
     * @return bool
     */
    public static function isJsonValid(string $data): bool
    {
        return self::validateStringAsJson($data)[0];
    }

    /**
     * Uses needed class properties as arguments/options for {@see self::encodeToJson()}.
     *
     * @param mixed $data
     * @param int $options
     * @return string
     */
    protected function encodeToJsonUseProps($data, int $options = 0): string
    {
        return self::encodeToJson($data, $options, $this->jsonRecursionDepth);
    }

    /**
     * Uses needed class properties as arguments/options for {@see self::encodeToJson()}.
     *
     * @param string $data
     * @param bool $assoc
     * @return mixed
     */
    protected function decodeJsonUseProps(string $data, bool $assoc = false)
    {
        return self::decodeJson($data, $assoc, $this->jsonRecursionDepth);
    }

    /**
     * Converts an object or an array to a recursive array.
     *
     * @param array|object $data
     * @return array
     */
    protected function convertToArray($data): array
    {
        return (array)($this->decodeJsonUseProps($this->encodeToJsonUseProps($data), true));
    }

    /**
     * Converts an array or an object to a recursive object.
     *
     * @param array $data
     * @param bool $forceObject Whether to convert indexed arrays to objects or not.
     * @return object|array
     */
    protected function convertToObject($data, bool $forceObject = false)
    {
        return $this->decodeJsonUseProps(
            $this->encodeToJsonUseProps($data, $forceObject ? JSON_FORCE_OBJECT : 0)
        );
    }

    /**
     * Gets value as an array or a scalar.
     * Converts a countable value to a recursive array, and return a scalar value as is. Also, this
     * method handles {@see JSON::OPT_JSON_DECODE_ALWAYS} option.
     *
     * @param mixed $value
     * @return mixed
     */
    protected function getOptimalValue($value)
    {
        if (self::isArrayOrObject($value)) {
            return $this->convertToArray($value);
        }

        // JSON::OPT_JSON_DECODE_ALWAYS handler
        if ($this->jsonDecodeAlways && is_string($value)) {
            // Validating JSON string
            try {
                return self::decodeJson($value);
            } catch (JsonException $e) {
            }
        }
        
        return $value;
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
        return $this->encodeToJsonUseProps($this->data, $options);
    }

    /**
     * Returns data as a recursive array.
     *
     * @return array
     */
    public function getDataAsArray(): array
    {
        return $this->convertToArray($this->data);
    }

    /**
     * Returns data as a recursive object.
     *
     * @return object|array
     */
    public function getDataAsObject()
    {
        return $this->convertToObject($this->data);
    }

    /**
     * Returns data as a completely-recursive object (i.e. even converts indexed arrays to objects).
     *
     * @return object
     */
    public function getDataAsFullObject(): object
    {
        return $this->convertToObject($this->data, true);
    }

    public function __toString(): string
    {
        return $this->getDataAsJsonString();
    }

    /**
     * Sets the return type that is used by other returning-value methods.
     *
     * @param int $type The type of returning values. For example, consider that you passed data as
     * an array and you pass this argument as JSON::TYPE_OBJECT; in this case, when you use
     * $json->get() (with no arguments, to get the data itself), then the data will be returned as
     * an object
     * @param bool $scalarAsIs To return all scalar data as scalar or not. Sometimes, methods reach
     * a scalar value, such as JSON::get(); in such cases, this argument determines that should the
     * returned data be scalar (i.e. as is) or not. If it sets to false, then all scalar data will
     * be returned as the type of $type (e.g. an array).
     * @return self
     */
    public function setReturnType(int $type = self::TYPE_DEFAULT, bool $scalarAsIs = true): self
    {
        switch ($type) {
            case self::TYPE_DEFAULT:
            case self::TYPE_JSON_STRING:
            case self::TYPE_ARRAY:
            case self::TYPE_OBJECT:
            case self::TYPE_FULL_OBJECT:
                $this->returnType = $type;
                $this->returnScalarAsScalar = $scalarAsIs;
                return $this;
                break;

            default:
                throw new InvalidArgumentException("Unknown return type");
        }
    }

    /**
     * Gets the value based on the return type.
     * Based on {@see self::$returnType} and {@see self::$returnScalarAsScalar}, converts the value
     * to the desired type (if needed) and returns it.
     *
     * @param mixed $value
     * @return mixed
     */
    protected function getValueBasedOnReturnType($value)
    {
        if (self::isScalar($value) && $this->returnScalarAsScalar) {
            return $value;
        }

        $returnType = $this->returnType;
        if ($returnType === self::TYPE_DEFAULT) {
            $returnType = $this->defaultDataType;
        }

        switch ($returnType) {
            case self::TYPE_JSON_STRING:
                return $this->encodeToJsonUseProps($value);
            case self::TYPE_ARRAY:
                return $this->convertToArray($value);
            case self::TYPE_OBJECT:
                return $this->convertToObject($value);
            case self::TYPE_FULL_OBJECT:
                return $this->convertToObject($value, true);
            default:
                throw new InvalidArgumentException("Unknown return type");
        }
    }

    /**
     * Follows keys to do (a) specific operation(s) with a specific element.
     * Crawl keys recursively, and find the specified element. Then, using a callable, do a specific
     * set of operations on the element.
     *
     * @param array $keys The keys to be crawled recursively. If you pass it an empty array, then
     * the method won't do anything.
     * @param array $data The data. It must be a recursive array or you may encounter errors.
     * @param callable $operation A set of operations to do with the element. The callable will get
     * the following argument(s):
     * 1. The specified element. You can get it by reference.
     * 2. The parent array of the element. You can get it by reference, too.
     * 3. The last key of the keys. You can access the element using it and the parent array.
     * The value returned by the callable will also be returned by this method.
     * @param bool $strictIndexing To throw exceptions when a key does not exist or create
     * non-exist a key. For example, you can set this to true when you want to get an element's
     * value (i.e. you want to ensure that the element exists).
     * @return mixed Return the return value of the callable ($operation).
     * @throws Exception When $strictIndexing is true but a key does not exist.
     * @throws UncountableValueException When a key contains a non-array (i.e. uncountable) value,
     * and thus, cannot crawling keys cannot be continued.
     */
    protected function crawlKeysRecursive(
        array $keys,
        array &$data,
        callable $operation,
        bool $strictIndexing = false
    ) {
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
            return $operation($data[$lastKey], $data, $lastKey);
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
     * Control special cases before calling {@see self::crawlKeysRecursive()}.
     * If $keys is an empty array, then pass make operation on the JSON::$data directly. This
     * method also prevent from passing scalar data by throwing an exception.
     *
     * @param $index Using JSON::extractIndex(), it will extract indexes too.
     * @see self::crawlKeysRecursive()
     * @throws ScalarDataException
     */
    protected function crawlKeys(
        string $index = null,
        callable $operation,
        bool $strictIndexing = false
    ) {
        $data = &$this->data;

        if (self::isScalar($data)) {
            throw new ScalarDataException("Cannot use the function on scalar data");
        }

        $keys = $this->extractIndex($index);
        if (count($keys) === 0) {
            return $operation($data);
        }
        return $this->crawlKeysRecursive($keys, $data, $operation, $strictIndexing);
    }

    /**
     * Extract keys from an index into an array by a delimiter.
     *
     * @param ?string $index
     * @param string $delimiter
     * @return array
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

        $keys = explode($delimiter, $index);

        // Set the escaped delimiters
        foreach ($keys as &$key) {
            $key = str_replace($replacement, $delimiter, $key);
        }

        return $keys;
    }

    /**
     * Gets an element's value.
     *
     * @param ?string $index Pass null if data is scalar.
     * @return mixed The value of the specified element. Returns null if the index cannot be found.
     * @throws ScalarDataException If data is scalar and $index is not null.
     */
    public function get(string $index = null)
    {
        if ($this->isDataScalar) {
            if ($index === null) {
                return $this->data;
            } else {
                throw new ScalarDataException("Indexing is invalid");
            }
        }

        try {
            return $this->crawlKeys($index, function ($element) {
                return $this->getValueBasedOnReturnType($element);
            }, true);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Sets an element to a value.
     *
     * @param mixed $value
     * @param ?string $index Pass null if data is scalar.
     * @return self
     * @throws ScalarDataException If data is scalar and $index is not null.
     */
    public function set($value, string $index = null): self
    {
        $value = $this->getOptimalValue($value);

        if ($this->isDataScalar) {
            if ($index === null) {
                $this->setDataTo($value);
                return $this;
            } else {
                throw new ScalarDataException("Indexing is invalid");
            }
        }

        $this->crawlKeys($index, function (&$element) use ($value) {
            $element = $value;
        });
        return $this;
    }

    /**
     * Unsets an element.
     *
     * @param string $index
     * @return self
     */
    public function unset(string $index): self
    {
        $this->crawlKeys($index, function ($e, &$data, $key) {
            // Unsetting the element directly is impossible
            unset($data[$key]);
        }, true);
        return $this;
    }

    /**
     * Determines if an element exists or not.
     *
     * @param string $index
     * @return bool Whether the element is set or not. A null value will be considered as not set.
     */
    public function isSet(string $index): bool
    {
        return $this->get($index) !== null;
    }

    /**
     * Replaces data with a new one.
     *
     * @param mixed $newData
     * @return mixed The previous data.
     */
    public function exchange($newData)
    {
        $prevData = $this->get();
        $this->__construct($newData, $this->options);
        return $prevData;
    }

    /**
     * Returns an element's value, if it is countable; otherwise, returns null.
     *
     * @param ?string $index Pass null if you want to get JSON::$data.
     * @return array|null
     */
    protected function getCountable(string $index = null)
    {
        return is_array($value = $this->get($index)) ? $value : null;
    }

    /**
     * Determines whether an element is countable or not.
     *
     * @param ?string $index Pass null if you want to check the data itself (i.e. you want to check
     * if data is scalar or not).
     * @return bool
     */
    public function isCountable(string $index = null): bool
    {
        return $this->getCountable($index) !== null;
    }

    /**
     * Counts the numbers elements in a countable element.
     *
     * @param ?string $index Pass null if you want to get the number of elements in the data itself.
     * @return int
     * @throws UncountableValueException
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

    /**
     * Iterates over an element.
     *
     * @param ?string $index
     * @return \Generator
     * @throws UncountableValueException
     */
    public function iterate(string $index = null): \Generator
    {
        // Get the value of the index in data
        if (($data = $this->getCountable($index)) === null) {
            throw new UncountableValueException("'$index' is not iterable");
        }

        foreach ($data as $key => $value) {
            yield $key => $this->getValueBasedOnReturnType($value);
        }
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

    public function offsetExists($index): bool
    {
        return $this->isSet((string)($index));
    }

    /**
     * Pushes a value to the end of a countable element.
     *
     * @param mixed $value
     * @param ?string $index Pass null if you want to push the value to the data root.
     * @return self
     * @throws UncountableValueException
     */
    public function push($value, string $index = null): self
    {
        $value = $this->getOptimalValue($value);

        try {
            $this->crawlKeys($index, function (&$element) use ($value) {
                array_push($element, $value);
            }, true);
        } catch (Exception $e) {
            throw new UncountableValueException("'$index' is not countable");
        }

        return $this;
    }

    /**
     * Pops the last value of a countable element from data.
     *
     * @param ?string $index Pass null if you want to pop from the data root.
     * @return self
     * @throws UncountableValueException
     */
    public function pop(string $index = null): self
    {
        try {
            $this->crawlKeys($index, function (&$element) {
                array_pop($element);
            }, true);
        } catch (Exception $e) {
            throw new UncountableValueException("'$index' is not countable");
        }

        return $this;
    }

    /**
     * Sets recursion depth when using json_*() functions.
     *
     * @param int $depth
     * @return self
     */
    public function setJsonRecursionDepth(int $depth): self
    {
        if ($depth < 1) {
            throw new InvalidArgumentException("Depth must be positive");
        }

        $this->jsonRecursionDepth = $depth;
        return $this;
    }
}
