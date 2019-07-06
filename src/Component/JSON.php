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

    // Merge options
    /**
     * @var int If it is set, when reaching duplicate keys, the new data keys will be replaced
     * instead of the countable in the (default) data. The default behaviour is to use the new data
     * values in these kinds of situations.
     */
    const MERGE_PREFER_DEFAULT_DATA = 1;

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
     * Finds a specific element using the keys and does (a) specific operation(s) on it.
     * Crawl keys recursively, and find the specified element. Then, using a callable, do a specific
     * set of operations on the element.
     *
     * @param array $keys The keys to be crawled recursively. If you pass it an empty array, then
     * the method won't do anything.
     * @param array $data The data. It must be a recursive array or you may encounter errors.
     * @param callable $function A set of operations to do with the element. The callable (that can
     * be a closure) accepts the following argument(s):
     * 1. The element's value; might be gotten by-reference.
     * 2. The parent element (that is an array); might be gotten by-reference.
     * 3. The last crawled key; the element might be accessed using it and the parent element
     * indirectly.
     * From within the callable, you can yield as many values as you want, and/or return some value.
     * @param bool $strictIndexing To throw exceptions when a key does not exist or create
     * non-exist a key. For example, you can set this to true when you want to get an element's
     * value (i.e. you want to ensure that the element exists).
     * @param bool $forceCountableValue Force the value be operated to be a countable one; so the
     * element passing to the function (i.e. callable) will be an array.
     * @return \Generator Return the return value of the callable ($operation).
     * @throws Exception When $strictIndexing is true but a key does not exist.
     * @throws UncountableValueException When a key contains a non-array (i.e. uncountable) value,
     * and thus, cannot crawling keys cannot be continued.
     * @throws UncountableValueException When $forceCountableValue is set to true but the reached
     * value is uncountable.
     */
    protected function &crawlKeysRecursive(
        array $keys,
        array &$data,
        callable $function,
        bool $strictIndexing = false,
        bool $forceCountableValue = false
    ): \Generator {
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
            $value = $data[$lastKey];

            if (!is_array($value) && $forceCountableValue) {
                throw new UncountableValueException("Expected countable, reached uncountable");
            }

            $result = $function($data[$lastKey], $data, $lastKey);
            if ($result instanceof \Generator) {
                foreach ($result as $key => &$value) {
                    yield $key => $value;
                }
                return $result->getReturn();
            }

            return $result;
            yield;
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
            return $this->crawlKeysRecursive($keys, $data[$curKey], $function, $strictIndexing);
        }
    }

    /**
     * Calls {@see self::crawlKeysRecursive()}, but with more features.
     * This method also prevent from passing scalar data by throwing an exception.
     *
     * @param $index Using JSON::extractIndex(), it will extract indexes too. Note that, If $index
     * is null, then $function gets only one argument, that is JSON::$data.
     * @throws ScalarDataException
     */
    protected function &crawlKeysGenerator(
        string $index = null,
        callable $function,
        bool $strictIndexing = false,
        bool $forceCountableValue = false
    ): \Generator {
        $data = &$this->data;

        if (self::isScalar($data)) {
            throw new ScalarDataException("Cannot use the function on scalar data");
        }

        $keys = $this->extractIndex($index);
        if (count($keys) === 0) {
            // Forcing the function to be a generator to prevent errors
            $generatorFunction = function () use ($function, $data) {
                return $function($data);
                yield;
            };
            $generator = $generatorFunction();
        } else {
            $generator = $this->crawlKeysRecursive(
                $keys,
                $data,
                $function,
                $strictIndexing,
                $forceCountableValue
            );
        }

        // Returning the generator itself
        return $generator;
    }

    /**
     * Calls {@see JSON::crawlKeysGenerator()} and returns its return value without yielding.
     * In many cases, to call the callable (i.e. $function) inside {@see JSON::crawlKeysRecursive()}
     * method, because of using generators, it is needed to return its value by calling
     * \Generator::getReturn(). Also, if the generator didn't return any values, null is returned.
     *
     * @return mixed
     */
    protected function crawlKeys(
        string $index = null,
        callable $function,
        bool $strictIndexing = false,
        bool $forceCountableValue = false
    ) {
        // Using try-catch for dealing with non-returning generator
        try {
            return $this->crawlKeysGenerator(
                $index,
                $function,
                $strictIndexing,
                $forceCountableValue
            )->getReturn();
        }
        // We don't want to catch all exceptions, though
        catch (Exception $e) {
            throw $e;
        } catch (\Exception $e) {
            return null;
        }
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
            // Un-setting the element directly is impossible
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
     * Determines whether an element is countable or not.
     *
     * @param ?string $index Pass null if you want to check the data itself (i.e. you want to check
     * if data is scalar or not).
     * @return bool
     */
    public function isCountable(string $index = null): bool
    {
        try {
            return $this->crawlKeys($index, function () {
                return true;
            }, true, true);
        } catch (UncountableValueException $e) {
            return false;
        }
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
        return $this->crawlKeys($index, function ($element) {
            return count($element);
        }, true, true);
    }

    /**
     * Iterates over a countable.
     *
     * @param ?string $index
     * @return \Generator
     * @throws UncountableValueException
     */
    public function &iterate(string $index = null): \Generator
    {
        $generator = $this->crawlKeysGenerator($index, function &(&$data) {
            foreach ($data as $key => &$value) {
                yield $key => $value;

                // Convert the value to an optimal one, e.g. convert objects to arrays
                $value = $this->getOptimalValue($value);
            }
        }, true, true);

        // Only yield from the generator, and don't allow the returning value to be returned
        foreach ($generator as $key => &$value) {
            yield $key => $value;
        }
    }

    /**
     * Calls a function on each member of a countable and returns its first non-null return value.
     *
     * @param callable $function The function to be called on each member until returning any
     * non-null values. It accepts the following arguments:
     * 1. The element's value; might be gotten by-reference.
     * 2. The element's key.
     * 3. The parent element; might be gotten by-reference.
     * @param string $index
     * @return mixed
     */
    public function forEach(callable $function, string $index = null)
    {
        return $this->crawlKeys($index, function (array &$data) use ($function) {
            foreach ($data as $key => &$value) {
                $result = $function($value, $key, $data);
                if ($result !== null) {
                    return $result;
                }
            }
        }, true, true);
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
     * Pushes a value to the end of a countable.
     *
     * @param mixed $value
     * @param ?string $index Pass null if you want to push the value to the data root.
     * @return self
     * @throws UncountableValueException
     */
    public function push($value, string $index = null): self
    {
        $value = $this->getOptimalValue($value);

        $this->crawlKeys($index, function (&$element) use ($value) {
            array_push($element, $value);
        }, true, true);
        return $this;
    }

    /**
     * Pops the last value of a countable and returns it.
     *
     * @param ?string $index Pass null if you want to pop from the data root.
     * @return self
     * @throws UncountableValueException
     */
    public function pop(string $index = null)
    {
        return $this->crawlKeys($index, function (&$element) {
            return array_pop($element);
        }, true, true);
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

    /**
     * Returns all values of a countable.
     *
     * @param string $index
     * @return array
     */
    public function getValues(string $index = null): array
    {
        return $this->crawlKeys($index, function (array $data) {
            return array_values($data);
        }, true, true);
    }

    /**
     * Returns all keys of a countable.
     *
     * @param string $index
     * @return array
     */
    public function getKeys(string $index = null): array
    {
        return $this->crawlKeys($index, function (array $data) {
            return array_keys($data);
        }, true, true);
    }

    /**
     * Returns a value from a countable in data.
     *
     * @param string $index
     * @return mixed
     */
    public function getRandomValue(string $index = null)
    {
        return $this->crawlKeys($index, function ($array) {
            return array_values($array)[random_int(0, count($array) - 1)];
        }, true, true);
    }

    /**
     * Returns a random key from an array in data.
     *
     * @param string $index
     * @return int|string
     */
    public function getRandomKey(string $index = null)
    {
        return $this->crawlKeys($index, function ($array) {
            return array_keys($array)[random_int(0, count($array) - 1)];
        }, true, true);
    }

    /**
     * Returns one or more random values from a countable in data.
     *
     * @param int $count
     * @param string $index
     * @return array
     */
    public function getRandomValues(int $count, string $index = null): array
    {
        return $this->crawlKeys($index, function ($array) use ($count) {
            $arrayValues = array_values($array);
            $arrayIndexLength = count($array) - 1;
            $randomValues = [];

            for ($i = 0; $i < $count; $i++) {
                $randomValues[] = $arrayValues[random_int(0, $arrayIndexLength)];
            }
            return $randomValues;
        }, true, true);
    }

    /**
     * Returns one or more random keys from a countable in data.
     *
     * @param integer $count
     * @param string $index
     * @return array
     */
    public function getRandomKeys(int $count, string $index = null): array
    {
        return $this->crawlKeys($index, function ($array) use ($count) {
            $arrayKeys = array_keys($array);
            $arrayIndexLength = count($array) - 1;
            $randomKeys = [];

            for ($i = 0; $i < $count; $i++) {
                $randomKeys[] = $arrayKeys[random_int(0, $arrayIndexLength)];
            }
            return $randomKeys;
        }, true, true);
    }

    /**
     * Applies a function to each member of a countable in data, recursively.
     *
     * @param callable $function The function to be called on each member, accepts three arguments:
     * 1. The element's value, might be gotten by-reference.
     * 2. The element's key.
     * 3. $extraValue, if passed.
     * @param string $index
     * @param mixed $extraData Extra data to be passed as third function argument.
     * @return self
     */
    public function walkRecursive(callable $function, string $index = null, $extraData = null): self
    {
        $this->crawlKeys($index, function ($array) use ($function, $extraData) {
            $result = @array_walk_recursive($array, $function, $extraData);
            if (!$result) {
                throw new Exception("Cannot walk through the array recursively");
            }
        }, true, true);
        return $this;
    }

    /**
     * Shifts an element off the beginning of an array in data.
     *
     * @param string $index
     * @return mixed The shifted element's value.
     */
    public function shift(string $index = null)
    {
        return $this->crawlKeys($index, function ($array) {
            return array_shift($array);
        }, true, true);
    }

    /**
     * Merges a countable in data with another countable data.
     *
     * @param mixed $newData The new data to be merged. Any values (except recourses) can be passed
     * and will be treated as an array.
     * @param int $options Can be one of the JSON::MERGE_* constants (not JSON::MERGE_R_* ones).
     * @param string $index
     * @return self
     */
    public function mergeWith($newData, int $options = 0, string $index = null): self
    {
        $newDataAsArray = (array)($this->getOptimalValue($newData));

        // Extracting options
        $reverseOrder = $options & self::MERGE_PREFER_DEFAULT_DATA;

        $this->crawlKeys($index, function (&$array) use ($newDataAsArray, $reverseOrder) {
            if ($reverseOrder) {
                $array = array_merge($newDataAsArray, $array);
            } else {
                $array = array_merge($array, $newDataAsArray);
            }
        }, true, true);
        return $this;
    }

    /**
     * Merges a countable in data with another countable data.
     *
     * @param mixed $newData The new data to be merged. Any values (except recourses) can be passed
     * and will be treated as an array.
     * @param int $options Can be one of the JSON::MERGE_R_* constants.
     * @param string $index
     * @return self
     */
    public function mergeRecursivelyWith($newData, int $options = 0, string $index = null): self
    {
        $newDataAsArray = (array)($this->getOptimalValue($newData));

        $this->crawlKeys($index, function (&$array) use ($newDataAsArray) {
            $array = array_merge_recursive($array, $newDataAsArray);
        }, true, true);
        return $this;
    }

    /**
     * Removes intersection of a countable with a new data.
     *
     * @param mixed $data The data to be compared with. Any values (except recourses) can be passed
     * and will be treated as an array.
     * @param bool $compareKeys To calculate intersection in the keys or not (i.e. in values).
     * @param string $index
     * @return self
     */
    public function removeIntersectionWith(
        $data,
        bool $compareKeys = false,
        string $index = null
    ): self {
        $dataAsArray = (array)($this->getOptimalValue($data));

        $this->crawlKeys($index, function (&$array) use ($dataAsArray, $compareKeys) {
            $array = $compareKeys ? array_diff_key($array, $dataAsArray)
                : array_diff($array, $dataAsArray);
        }, true, true);
        return $this;
    }

    /**
     * Filters a countable using a callable.
     *
     * @param callable $function The function to be called on each member of the countable. It
     * should return a boolean, or any non-false values is considered as true (i.e. any value
     * that loosely equals false is considered as false, such as 0); it accepts two arguments:
     * 1. The element's value.
     * 2. The element's key.
     * Keep in mind, the given value by the callable is safe from overwriting; so getting it
     * by-reference or not does not matter.
     * @param string $index
     * @return array
     */
    public function filter(callable $function, string $index = null): array
    {
        return $this->crawlKeys($index, function (array $data) use ($function) {
            $filteredArray = [];
            foreach ($data as $key => $value) {
                if ($function($value, $key)) {
                    $filteredArray[$key] = $value;
                }
            }
            return $filteredArray;
        }, true, true);
    }

    /**
     * Flips values and keys in a countable.
     *
     * @param string $index
     * @return self
     */
    public function flipValuesAndKeys(string $index = null): self
    {
        $this->crawlKeys($index, function (array &$data) {
            $data = array_flip($data);
        }, true, true);
        return $this;
    }

    /**
     * Reduces a countable to a single value.
     *
     * @param callable $function Read array_reduce() documentation for more information.
     * @param string $index
     * @return mixed
     */
    public function reduce(callable $function, string $index = null)
    {
        return $this->crawlKeys($index, function (array $data) use ($function) {
            return array_reduce($data, $function);
        }, true, true);
    }
}
