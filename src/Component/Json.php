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
use MAChitgarha\Exception\JSON\JsonException;
use MAChitgarha\Exception\JSON\OverflowException;

/**
 * Handles JSON data type.
 *
 * Gets a JSON string or a PHP native array or object and handles it as a JSON data.
 *
 * @see https://github.com/MAChitgarha/JSON/wiki
 * @see https://github.com/MAChitgarha/JSON/wiki/Glossary
 */
class Json implements \ArrayAccess
{
    /**
     * @var array|int|string|float|bool|null JSON data as a complete native PHP array (to be
     * handled more easily), or as a scalar type. It is important to note that what we mean from a
     * scalar type can be NULL, also. If the data passed to the constructor is countable, then the
     * data will be saved as an array; otherwise, if it's scalar, it will be saved as is. Also,
     * these allowed types can be in a string that contains a valid JSON data.
     */
    protected $data;

    /** @var bool A scalar type could be an integer, a string, a float, a boolean, or NULL. */
    protected $isDataScalar = false;

    /** @var int Default data type; can be one of the JSON::TYPE_* constants. */
    protected $defaultDataType = self::TYPE_JSON_STRING;

    /** @var int {@see self::setReturnType()}. */
    protected $returnType = self::TYPE_ARRAY;

    ### Options and customizations

    /** @var int Options passed to the constructor. */
    protected $options = 0;

    /** @var bool {@see self::OPT_JSON_DECODE_ALWAYS} */
    protected $jsonDecodeAlways = false;

    /** @var int {@see self::setJsonRecursionDepth()} */
    protected $jsonRecursionDepth = 512;

    /** @var bool {@see self::setReturnType()}. */
    protected $returnScalarAsScalar = true;

    /** @var callable {@see self::setRandomizationFunction()}. */
    protected $randomizationFunction = "mt_rand";

    ### Data types
    /** @var int Type of data passed to the constructor. */
    const TYPE_DEFAULT = 0;
    /** @var int JSON string data type. */
    const TYPE_JSON_STRING = 1;
    /** @var int Array data type (recursive). This is probably the most efficient way. */
    const TYPE_ARRAY = 3;
    /** @var int Object data type (recursive), without converting indexed arrays to objects. */
    const TYPE_OBJECT = 2;
    /** @var int Object data type (recursive), with converting even indexed arrays to objects. */
    const TYPE_FULL_OBJECT = 4;

    ### Indexing types
    /**
     * @var int If a key cannot be found, create it. If a key contains an uncountable value,
     * override it (i.e. remove its value and convert it to a countable value), This indexing type
     * could be dangerous; as it removes everything.
     */
    const INDEXING_FREE = 0;
    /**
     * @var int If a key cannot be found, create it. If a key contains an uncountable value,
     * throw a new exception and don't continue.
     */
    const INDEXING_SAFE = 1;
    /**
     * @var int If a key cannot be found or a key contains an uncountable value, throw an exception.
     * This indexing type is really useful when you want to get the element's value, for example.
     */
    const INDEXING_STRICT = 2;

    ### Merge options
    /**
     * @var int If it is set, when reaching duplicate keys, the new data keys will be replaced
     * instead of the countable in the (default) data. The default behaviour is to use the new data
     * values in these kinds of situations.
     */
    const MERGE_PREFER_DEFAULT_DATA = 1;

    ### Options
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
        $this->setOptions($options);

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
            $this->data = $data;
            return;
        }

        if (($treatAsJsonString || !$treatAsString) && $isString) {
            list($isJsonValid, $decodedData) = $this->validateStringAsJson($data, true);

            if (!$isJsonValid && $treatAsJsonString) {
                throw new InvalidJsonException();
            }
            
            if ($isJsonValid) {
                $this->defaultDataType = self::TYPE_JSON_STRING;
                $this->data = $decodedData;
                return;
            }
        }

        /*
         * The code will NOT reach here, if, JSON::OPT_TREAT_AS_JSON_STRING is enabled. So, the
         * code will reach here only if one of the following things happen:
         * 1. JSON::OPT_TREAT_AS_STRING is enabled.
         * 2. JSON::OPT_TREAT_AS_STRING is not enabled, and data is not a valid JSON string.
         */
        if (self::isScalar($data)) {
            $this->data = $treatAsString ? (string)($data) : $data;
            return;
        }

        // If data is invalid, i.e. is a resource
        throw new InvalidArgumentException("Data must be either countable or scalar");
    }

    /**
     * Creates a new instance of the class.
     *
     * @return self
     */
    public static function new($data = [], int $options = 0): self
    {
        return new self($data, $options);
    }

    ### Changing options and customizing

    /**
     * Sets options used by methods.
     *
     * @param int $options Can be one of the JSON::OPT_* constants.
     * @return self
     */
    public function setOptions(int $options = 0): self
    {
        $this->options = $options;

        $this->jsonDecodeAlways = (bool)($options & self::OPT_JSON_DECODE_ALWAYS);

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

    /**
     * Sets the randomization function, used when a random integer is needed.
     *
     * @param callable $function It accepts two arguments:
     * 1. The minimum value to be returned,
     * 2. The maximum value to be returned;
     * It must return an integer. You can also use built-in functions like mt_rand().
     * @return self
     */
    public function setRandomizationFunction(callable $function): self
    {
        // For sure, the function is callable, because it is defined as callable
        $this->randomizationFunction = $function;

        $randomValue = $function(0, 1);
        if ($randomValue !== 0 && $randomValue !== 1) {
            throw new InvalidArgumentException("Return value of \$function is invalid");
        }

        return $this;
    }

    /**
     * Sets the return type that is used by other returning-value methods.
     *
     * @param int $type The type of returning values. For example, consider that you passed data as
     * an array and you pass this argument as JSON::TYPE_OBJECT; in this case, when you use
     * JSON::get() (with no arguments, to get the data itself), then the data will be returned as
     * an object.
     * @param bool $scalarAsIs To return all scalar data as scalar or not. Sometimes, methods
     * returning a value, reach a scalar value, such as JSON::get(). In these cases, this argument
     * determines that should the returned value be scalar or not. If it sets to false, then all
     * scalar data will be returned as the type of $type (e.g. an array).
     * @return self
     */
    public function setReturnType(int $type = self::TYPE_ARRAY, bool $scalarAsIs = true): self
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
        if ($this->returnScalarAsScalar && self::isScalar($value)) {
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
     * Decodes a valid JSON string and returns it if {@see JSON::OPT_JSON_DECODE_ALWAYS} is enabled.
     *
     * @param mixed $value
     * @return mixed
     */
    protected function toJsonIfNeeded($value)
    {
        if ($this->jsonDecodeAlways && is_string($value)) {
            // Validating JSON string
            try {
                $value = self::decodeJson($value, true);
            } catch (JsonException $e) {
            }
        }
        return $value;
    }

    /**
     * Returns a random value by calling {@see self::$randomizationFunction}.
     *
     * @param int $min
     * @param int $max
     * @return int
     * @see self::setRandomizationFunction()
     */
    protected function randomInt(int $min, int $max): int
    {
        return ($this->randomizationFunction)($min, $max);
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
        self::handleJsonErrors(json_last_error());
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
        self::handleJsonErrors(json_last_error());
        return $decodedData;
    }

    /**
     * Handles JSON errors and throw exceptions, if needed.
     *
     * @param integer $jsonErrorStat The return value of json_last_error().
     * @return void
     */
    protected static function handleJsonErrors(int $jsonErrorStat)
    {
        switch ($jsonErrorStat) {
            case JSON_ERROR_NONE:
                return;
                break;
            
            case JSON_ERROR_DEPTH:
                $message = "Maximum stack depth exceeded";
                break;

            case JSON_ERROR_STATE_MISMATCH:
            case JSON_ERROR_SYNTAX:
                $message = "Invalid or malformed JSON";
                break;
            
            case JSON_ERROR_CTRL_CHAR:
            case JSON_ERROR_UTF8:
            case JSON_ERROR_UTF16:
                $message = "Malformed characters, possibly incorrectly encoded JSON";
                break;

            case JSON_ERROR_INF_OR_NAN:
                $message = "NAN and INF cannot be encoded";
                break;
            
            case JSON_ERROR_INVALID_PROPERTY_NAME:
                $message = "Found an invalid property name";
                break;
            
            case JSON_ERROR_UNSUPPORTED_TYPE:
                $message = "A value cannot be encoded, possibly is a resource";
                break;
            
            default:
                $message = "Unknown JSON error";
                break;
        }

        throw new JsonException($message);
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
        return (new \ArrayObject($data))->getArrayCopy();
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
     * Finds a specific element using $keys and calls a function on it.
     *
     * @param array $keys The keys to be followed recursively.
     * @param array $data The data. It must be a recursive array or you may encounter errors.
     * @return array{0:array|mixed,1:array|array[],2:mixed}|array{0:array|array[],1:null,2:null}
     * @see JSON::do()
     */
    protected function &findElementRecursive(
        array $keys,
        &$data,
        bool $forceCountableValue = false,
        int $indexingType = self::INDEXING_STRICT,
        bool $isDataUnreliable = true
    ): array {
        $keysCount = count($keys);

        if ($keysCount > 1) {
            // Get the current key, and remove it from keys array
            $curKey = array_shift($keys);

            if (isset($data[$curKey])) {
                $childData = &$data[$curKey];

                if ($isDataUnreliable && is_object($childData)) {
                    $childData = $this->convertToArray($childData);
                    $isDataUnreliable = false;
                }

                if (!is_array($childData) && $indexingType >= self::INDEXING_SAFE) {
                    throw new UncountableValueException("The key '$curKey' has uncountable value");
                }
            } else {
                if ($indexingType === self::INDEXING_STRICT) {
                    throw new Exception("The key '$curKey' does not exist");
                }
                $data[$curKey] = [];
            }

            // Recursion
            return $this->findElementRecursive(
                $keys,
                $data[$curKey],
                $forceCountableValue,
                $indexingType,
                $isDataUnreliable
            );
        }

        // End of recursion
        else {
            if ($keysCount === 1) {
                $lastKey = $keys[0];

                if (isset($data[$lastKey])) {
                    $childData = &$data[$lastKey];

                    if ($isDataUnreliable && is_object($childData)) {
                        $childData = $this->convertToArray($childData);
                        $isDataUnreliable = false;
                    }
                    if ($forceCountableValue && !is_array($childData)) {
                        throw new UncountableValueException("Cannot use the method on uncountable");
                    }
                } else {
                    if ($indexingType === self::INDEXING_STRICT) {
                        throw new Exception("The key '$lastKey' does not exist");
                    }
                    $data[$lastKey] = null;
                }
    
                $returnValue = [
                    &$data[$lastKey],
                    &$data,
                    $lastKey,
                ];
            }
            // If $keysCount is 0
            else {
                if ($forceCountableValue && self::isScalar($data)) {
                    throw new UncountableValueException("Cannot use the method on uncountable");
                }

                $returnValue = [
                    &$data,
                    null,
                    null,
                ];
            }

            return $returnValue;
        }
    }

    /**
     * Extract index to an array of keys using a delimiter.
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
     * Finds a specific element using $index and calls a function on it.
     *
     * @param ?callable $function It accepts the following argument(s):
     * 1. The element's value; might be gotten by-reference.
     * 2. Whether the the element is data root or not.
     * 3. The parent element (that is an array); might be gotten by-reference.
     * 4. The last key in the index; might be used to access the element (using the parent element).
     * From within the callable, you can yield as many values as you want, and/or return a value.
     * The return type of the method will be exactly the return type of this callable. Note that if
     * $index is null, the first argument will be the only passing argument.
     * @param ?string $index The index of the element to be found, and it's extracted as keys. Pass
     * null if you want to get the data root inside the callback.
     * @param bool $forceCountableValue Force the value be operated to be a countable one, so, the
     * element (i.e. first argument) passing to $function will be an array.
     * @param bool $extractIndex Whether to extract $index to keys or not. The extraction of $index
     * is performed using dots.
     * @param int $indexingType Can be one of the JSON::INDEXING_* constants.
     * @return mixed The return value of $function, whether is a generator or not.F
     * @throws Exception When reaching a key that does not exist and $strictIndexing is true.
     * @throws UncountableValueException If reaching a key that contains an uncountable value.
     * @throws UncountableValueException When reaching an uncountable element and
     * $forceCountableValue is set to true.
     * @throws UncountableValueException If data is scalar and $forceCountableValue is set to true.
     * @todo Remove the at sign operator.
     */
    public function &do(
        callable $function = null,
        string $index = null,
        bool $forceCountableValue = false,
        int $indexingType = self::INDEXING_STRICT,
        bool $extractIndex = true
    ) {
        $data = &$this->data;
        if (is_object($data)) {
            $data = $this->convertToArray($data);
        }

        if (self::isScalar($data) && $index !== null) {
            throw new UncountableValueException("Cannot use indexing on uncountable");
        }

        // On debugging, pay attention to the following @ operator!
        @$returnValueReference = &$function(... $this->findElementRecursive(
            $extractIndex ? $this->extractIndex($index) : (array)($index),
            $data,
            $forceCountableValue,
            $indexingType
        ));
        return $returnValueReference;
    }

    /**
     * Gets an element inside data.
     *
     * @param string $index
     * @param integer $indexingType Can be one of the JSON::INDEXING_* constants.
     * @return JSONChild
     */
    public function index(string $index, int $indexingType = self::INDEXING_FREE): JSONChild
    {
        return new JSONChild($this->do(function &(&$element) {
            return $element;
        }, $index, false, $indexingType), get_object_vars($this));
    }

    /**
     * Returns an element's value.
     *
     * @param ?string $index Pass null if data is scalar.
     * @return mixed The value of the specified element. Returns null if the index cannot be found.
     */
    public function get(string $index = null)
    {
        try {
            return $this->do(function ($value) {
                return $this->getValueBasedOnReturnType($value);
            }, $index);
        } catch (UncountableValueException $e) {
            throw $e;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Returns an element by-reference.
     *
     * @param string $index
     * @return mixed
     */
    public function &getByReference(string $index = null)
    {
        return $this->do(function &(&$element) {
            $element = $this->getValueBasedOnReturnType($element);
            return $element;
        }, $index);
    }

    /**
     * Sets an element to a value.
     *
     * @param mixed $value
     * @param ?string $index Pass null if data is scalar.
     * @return self
     */
    public function set($value, string $index = null): self
    {
        $this->do(function (&$element) use ($value) {
            $element = $this->toJsonIfNeeded($value);
        }, $index, false, self::INDEXING_FREE);
        return $this;
    }

    /**
     * Determines if an element exists or not.
     *
     * @param string $index
     * @return bool Whether the element is set or not. A null value will be considered as not set.
     */
    public function isSet(string $index = null): bool
    {
        return $this->get($index) !== null;
    }

    /**
     * Unsets an element.
     *
     * @param string $index
     * @return self
     */
    public function unset(string $index = null): self
    {
        $this->do(function (&$element, &$data, $key) {
            $element = null;
            // Un-setting the element directly is impossible
            unset($data[$key]);
        }, $index);
        return $this;
    }

    private function throwExceptionArrayAccessOnScalar()
    {
        if (self::isScalar($this->data)) {
            throw new UncountableValueException("Array access is not possible on scalar data");
        }
    }

    public function &offsetGet($index)
    {
        $this->throwExceptionArrayAccessOnScalar();
        return $this->data[$index];
    }

    public function offsetSet($index, $value)
    {
        $this->throwExceptionArrayAccessOnScalar();
        $this->data[$index] = $this->toJsonIfNeeded($value);
    }

    public function offsetExists($index): bool
    {
        $this->throwExceptionArrayAccessOnScalar();
        return isset($this->data[$index]);
    }

    public function offsetUnset($index)
    {
        $this->throwExceptionArrayAccessOnScalar();
        unset($this->data[$index]);
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
     * @param ?string $index Pass null if you want to check the data itself (i.e. checking if it is
     * scalar or not).
     * @return bool
     */
    public function isCountable(string $index = null): bool
    {
        try {
            return $this->do(function () {
                return true;
            }, $index, true);
        } catch (UncountableValueException $e) {
            return false;
        }
    }

    /**
     * Counts the numbers elements in a countable element.
     *
     * @param ?string $index Pass null if you want to get the number of elements in the data root.
     * @return int
     */
    public function count(string $index = null): int
    {
        return $this->do(function ($element) {
            return count($element);
        }, $index, true);
    }

    /**
     * Converts the data to an array, if it is scalar.
     *
     * @return self
     */
    public function toCountable(): self
    {
        $this->data = (array)($this->data);
        return $this;
    }

    /**
     * Iterates over a countable.
     *
     * @param ?string $index
     * @return \Generator
     */
    public function &iterate(string $index = null): \Generator
    {
        return $this->do(function &(array &$array) {
            foreach ($array as $key => &$value) {
                // E.g. convert arrays to objects
                $value = $this->getValueBasedOnReturnType($value);

                yield $key => $value;

                $value = $this->toJsonIfNeeded($value);
            }
        }, $index, true);
    }

    /**
     * Iterates over a countable, but returns its value as a new JSON class.
     *
     * @param string $index
     * @return \Generator
     */
    public function iterateAsJson(string $index = null): \Generator
    {
        $objectVars = get_object_vars($this);

        return $this->do(function (array &$array) use ($objectVars) {
            foreach ($array as $key => &$value) {
                yield $key => new JSONChild($value, $objectVars);
            }
        }, $index, true);
    }

    /**
     * Calls a function on each member of a countable and returns its first non-null return value.
     *
     * @param callable $function The function to be called on each member until returning any
     * non-null values. It accepts the following arguments:
     * 1. The element's value; might be gotten by-reference.
     * 2. The element's key.
     * 3. The parent element; might be gotten by-reference.
     * @param ?string $index
     * @return mixed
     */
    public function forEach(callable $function, string $index = null)
    {
        return $this->do(function (array &$array) use ($function) {
            foreach ($array as $key => &$value) {
                $result = $function($value, $key, $array);
                // Returning the first non-null value
                if ($result !== null) {
                    return $result;
                }
            }
        }, $index, true);
    }

    /**
     * Applies a function recursively to every member of a countable.
     *
     * @param callable $function The function to be called on each member, accepts three arguments:
     * 1. The element's value, might be gotten by-reference.
     * 2. The element's key.
     * @param ?string $index
     * @return self
     */
    public function forEachRecursive(callable $function, string $index = null): self
    {
        $this->do(function (array &$array) use ($function) {
            $this->walkRecursive($array, $function);
        }, $index, true);
        return $this;
    }

    /**
     * Iterates a countable recursively and applies a function on its each member.
     *
     * @param array $array
     * @param callable $function {@see self::forEachRecursive()}
     * @return self
     */
    protected function walkRecursive(array &$array, callable $function): self
    {
        foreach ($array as $key => &$value) {
            if (is_array($value)) {
                // Recursion
                $this->walkRecursive($value, $function);
            } else {
                $function($value, $key);
            }
        }
        return $this;
    }

    /**
     * Pushes a value to the end of a countable.
     *
     * @param mixed $value
     * @param ?string $index Pass null if you want to push the value to the data root.
     * @return self
     */
    public function push($value, string $index = null): self
    {
        $this->do(function (array &$array) use ($value) {
            array_push($array, $this->toJsonIfNeeded($value));
        }, $index, true);
        return $this;
    }

    /**
     * Pops the last value of a countable and returns it.
     *
     * @param ?string $index Pass null if you want to pop from the data root.
     * @return mixed The removed element's value.
     */
    public function pop(string $index = null)
    {
        return $this->do(function (array &$array) {
            return array_pop($array);
        }, $index, true);
    }

    /**
     * Removes the first element of a countable.
     *
     * @param ?string $index Pass null if you want to remove an element from the data root.
     * @return mixed The removed element's value.
     */
    public function shift(string $index = null)
    {
        return $this->do(function (array $array) {
            return array_shift($array);
        }, $index, true);
    }

    /**
     * Prepends an element to a countable.
     *
     * @param ?string $index Pass null if you want to prepend an element to the data root.
     * @return self
     */
    public function unshift($value, string $index = null): self
    {
        $this->do(function (array &$array) use ($value) {
            array_unshift($array, $value);
        }, $index, true);
        return $this;
    }

    /**
     * Returns all values of a countable.
     *
     * @param ?string $index
     * @return array
     */
    public function getValues(string $index = null): array
    {
        return $this->do(function (array $array) {
            return array_values($array);
        }, $index, true);
    }

    /**
     * Returns all keys of a countable.
     *
     * @param ?string $index
     * @return array
     */
    public function getKeys(string $index = null): array
    {
        return $this->do(function (array $array) {
            return array_keys($array);
        }, $index, true);
    }

    /**
     * Returns a random value from a countable.
     *
     * @param ?string $index
     * @return mixed
     */
    public function getRandomValue(string $index = null)
    {
        return $this->do(function (array $array) {
            return array_values($array)[$this->randomInt(0, count($array) - 1)];
        }, $index, true);
    }

    /**
     * Returns a random key from a countable.
     *
     * @param ?string $index
     * @return int|string
     */
    public function getRandomKey(string $index = null)
    {
        return $this->do(function (array $array) {
            return array_keys($array)[$this->randomInt(0, count($array) - 1)];
        }, $index, true);
    }

    /**
     * Returns a random key/value pair from a countable
     *
     * @param ?string $index
     * @return array{0:string|int,1:mixed} An array that contains the element's key and the
     * element's value, respectively. You can get it as a list: list($key, $value). You can
     * also get the value by-reference.
     */
    public function getRandomElement(string $index = null): array
    {
        return $this->do(function (array &$array) {
            $randomKey = array_keys($array)[$this->randomInt(0, count($array) - 1)];
            $returnValue = [
                $randomKey,
                &$array[$randomKey]
            ];
            return $returnValue;
        }, $index, true);
    }

    /**
     * Returns one or more random values from a countable in data.
     *
     * @param int $count
     * @param ?string $index
     * @return array
     */
    public function getRandomValues(int $count, string $index = null): array
    {
        return $this->do(function (array $array) use ($count) {
            $maxArrayIndex = count($array) - 1;

            if ($count > $maxArrayIndex) {
                throw new OverflowException("Count of values must not reach the countable length");
            }

            $arrayKeys = array_keys($array);

            $randomValues = [];
            while (count($randomValues) < $count) {
                $randomValues[] = $array[$arrayKeys[$this->randomInt(0, $maxArrayIndex)]];
            }
        
            return $randomValues;
        }, $index, true);
    }

    /**
     * Returns one or more random keys from a countable in data.
     *
     * @param integer $count
     * @param ?string $index
     * @return array
     */
    public function getRandomKeys(int $count, string $index = null): array
    {
        return $this->do(function (array $array) use ($count) {
            $maxArrayIndex = count($array) - 1;

            if ($count > $maxArrayIndex) {
                throw new OverflowException("Count of keys must not reach the countable length");
            }

            $arrayKeys = array_keys($array);

            $randomKeys = [];
            while (count($randomKeys) < $count) {
                $randomKeys[] = $arrayKeys[$this->randomInt(0, $maxArrayIndex)];
            }
        
            return $randomKeys;
        }, $index, true);
    }

    /**
     * Returns a random subset of a countable.
     *
     * @param integer $size Subset's length.
     * @param ?string $index
     * @return array
     */
    public function getRandomSubset(int $size, string $index = null): array
    {
        return $this->do(function (array &$array) use ($size) {
            $maxArrayIndex = count($array) - 1;

            if ($size > $maxArrayIndex) {
                throw new OverflowException("Subset size must be lower than countable size");
            }

            $arrayKeys = array_keys($array);

            $randomSubset = [];
            while (count($randomSubset) < $size) {
                $randomKey = $arrayKeys[$this->randomInt(0, $maxArrayIndex)];
                $randomSubset[$randomKey] = $array[$randomKey];
            }
        
            return $randomSubset;
        }, $index, true);
    }

    /**
     * Merges a countable in data with another countable data.
     *
     * @param mixed $newData The new data to be merged. Any values (except recourses) can be passed
     * and will be treated as an array.
     * @param int $options Can be one of the JSON::MERGE_* constants (not JSON::MERGE_R_* ones).
     * @param ?string $index
     * @return self
     */
    public function mergeWith($newData, int $options = 0, string $index = null): self
    {
        // Extracting options
        $reverseOrder = $options & self::MERGE_PREFER_DEFAULT_DATA;

        $this->do(function (array &$array) use ($newData, $reverseOrder) {
            $newData = (array)($this->toJsonIfNeeded($newData));
            if ($reverseOrder) {
                $array = array_merge($newData, $array);
            } else {
                $array = array_merge($array, $newData);
            }
        }, $index, true);
        return $this;
    }

    /**
     * Merges a countable in data with another countable data.
     *
     * @param mixed $newData The new data to be merged. Any values (except recourses) can be passed
     * and will be treated as an array.
     * @param int $options Can be one of the JSON::MERGE_R_* constants.
     * @param ?string $index
     * @return self
     */
    public function mergeRecursivelyWith($newData, int $options = 0, string $index = null): self
    {
        $this->do(function (array &$array) use ($newData) {
            $newData = (array)($this->toJsonIfNeeded($newData));
            $array = array_merge_recursive($array, $newData);
        }, $index, true);
        return $this;
    }

    /**
     * Removes intersection of a countable with a another countable.
     *
     * @param mixed $data The data to be compared with. Any values (except recourses)
     * can be passed and will be treated as an array.
     * @param bool $compareKeys To calculate intersection in the keys or not (i.e. in values).
     * @param ?string $index
     * @return self
     * @see https://gist.github.com/nunoveloso/1992851 Thanks to this.
     */
    public function difference(
        $data,
        bool $compareKeys = false,
        string $index = null
    ): self {
        $data = (array)($this->toJsonIfNeeded($data));

        $diff = function (array $array) use ($data) {
            $diff = array();
  
            foreach ($array as $value) {
                $diff[$value] = 1;
            }
            foreach ($data as $value) {
                unset($diff[$value]);
            }
          
            return array_keys($diff);
        };

        $diffKey = function (array $array) use ($data) {
            $diff = array();

            foreach ($array as $key => $value) {
                $diff[$key] = $value;
            }
            foreach ($data as $key => $value) {
                unset($diff[$key]);
            }
          
            return $diff;
        };

        $this->do(function (array &$array) use ($compareKeys, $diff, $diffKey) {
            $array = $compareKeys ? $diff($array) : $diffKey($array);
        }, $index, true);
        return $this;
    }

    /**
     * Filters a countable using a callable.
     *
     * @param ?callable $function The function to be called on each member of the countable. It
     * should return a boolean, or any non-false values is considered as true (i.e. any value
     * that loosely equals false is considered as false, such as 0); it accepts two arguments:
     * 1. The element's value.
     * 2. The element's key.
     * Keep in mind, the given value by the callable is safe from overwriting; so getting it
     * by-reference or not does not matter. The default function removes all null values.
     * @param ?string $index
     * @return self
     */
    public function filter(callable $function = null, string $index = null): self
    {
        if ($function === null) {
            $function = function ($value) {
                return $value !== null;
            };
        }

        $this->do(function (array &$array) use ($function) {
            $filteredArray = [];
            foreach ($array as $key => $value) {
                if ($function($value, $key)) { // @phan-suppress-current-line PhanParamTooMany
                    $filteredArray[$key] = $value;
                }
            }
            $array = $filteredArray;
        }, $index, true);
        return $this;
    }

    /**
     * Flips values and keys in a countable.
     *
     * @param ?string $index
     * @return self
     */
    public function flipValuesAndKeys(string $index = null): self
    {
        $this->do(function (array &$array) {
            $array = array_flip($array);
        }, $index, true);
        return $this;
    }

    /**
     * Reduces a countable to a single value.
     *
     * @param callable $function Read array_reduce() documentation for more information.
     * @param ?string $index
     * @return mixed
     */
    public function reduce(callable $function, string $index = null)
    {
        return $this->do(function (array $array) use ($function) {
            return array_reduce($array, $function);
        }, $index, true);
    }

    /**
     * Shuffles a countable.
     *
     * @param ?string $index
     * @return self
     * @see https://stackoverflow.com/a/32035692/4215651 Thanks to this.
     */
    public function shuffle(string $index = null): self
    {
        $this->do(function (array &$array) {
            $shuffledArray = [];
            $arrayIndexLength = count($array) - 1;

            while (count($shuffledArray) <= $arrayIndexLength) {
                $randomKey = ($this->randomizationFunction)(0, $arrayIndexLength);
                $shuffledArray[$randomKey] = $array[$randomKey];
            }

            $array = $shuffledArray;
        }, $index, true);
        return $this;
    }

    /**
     * Reverses a countable.
     *
     * @param ?string $index
     * @return self
     */
    public function reverse(string $index = null): self
    {
        $this->do(function (array &$array) {
            $array = array_reverse($array);
        }, $index, true);
        return $this;
    }

    /**
     * Fills an element with a value.
     *
     * @param int $startIndex
     * @param int $length
     * @param mixed $value
     * @param ?string $index
     * @return self
     */
    public function fill(int $startIndex, int $length, $value, string $index = null): self
    {
        $this->do(function (&$element) use ($startIndex, $length, $value) {
            $element = array_fill($startIndex, $length, $value);
        }, $index, false, self::INDEXING_FREE);
        return $this;
    }

    /**
     * Returns the first key of a countable.
     *
     * @param ?string $index
     * @return string|int
     */
    public function getFirstKey(string $index = null)
    {
        return $this->do(function (array $array) {
            foreach ($array as $key => $value) {
                return $key;
            }
        }, $index, true);
    }

    /**
     * Returns the last key of a countable.
     *
     * @param ?string $index
     * @return string|int
     * @see https://stackoverflow.com/a/7478419/4215651 Thanks to this.
     */
    public function getLastKey(string $index = null)
    {
        return $this->do(function (array $array) {
            return key(array_slice($array, -1, 1, true));
        }, $index, true);
    }
}
