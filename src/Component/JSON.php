<?php

/**
 * @author Mohammad Amin Chitgarha <machitgarha@outlook.com>
 * @see https://github.com/MAChitgarha
 */
namespace MAChitgarha\Component;

use Zend\Json\Exception\InvalidArgumentException;


/**
 * Handles JSON data type.
 * 
 * Gets a JSON string or a PHP native array or object and handles it as a JSON data.
 * 
 * @see https://github.com/MAChitgarha/JSON
 * @todo Extend from \ArrayObject.
 */
class JSON
{
    /** @var string Holds JSON data as a native PHP data (either object or array) */
    protected $data;

    // Data types
    /** @var int The data without any type changes */
    const TYPE_DEFAULT = 0;
    /** @var int The data in the format of JSON string */
    const TYPE_JSON = 1;
    /** @var int The data as an object */
    const TYPE_OBJECT = 2;
    /** @var int The data as an array */
    const TYPE_ARRAY = 3;
    /** @var int In crawling indexes, an index must be existed, otherwise, an exception will be thrown. */
    const STRICT_INDEXING = 4;

    /**
     * Prepares JSON data.
     * 
     * @param string|array|object $data The data; can be either a JSON string, array or object.
     * A JSON string must be a valid JSON object or array, and must not be a boolean, for example. It should not contain any closures, otherwise, they will be considered as empty objects.
     * @throws \InvalidArgumentException When data is not a valid JSON (as described), an array or an object.
     */
    public function __construct($data = []) {
        $isString = is_string($data);
        $isArray = is_array($data);
        $isObject = is_object($data);
        /** @var bool $isGoodJson Check if JSON data is object or array */
        $isGoodJson = $isString ? in_array(gettype(json_decode($data)), ["array", "object"]) :
            false;
        
        // Force data to be an array or object, either native or JSON.
        if (!($isGoodJson || $isArray || $isObject))
            throw new \InvalidArgumentException("Wrong data type");

        // Convert data to an array
        if ($isString)
            $data = json_decode($data);
        $this->data = $data;
    }

    /**
     * Returns data as the determined type.
     *
     * @param integer $type The type to return.
     * @param boolean $recursive Force $type as the type for all sub-values. No effects when the $type is TYPE_DEFAULT or TYPE_JSON.
     * @return string|array|object
     * @throws \InvalidArgumentException If the requested type is unknown.
     */
    public function getData(int $type = self::TYPE_DEFAULT, bool $recursive = true)
    {
        switch ($type) {
            case self::TYPE_DEFAULT:
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
     * Returns data as JSON.
     *
     * @param int $options The options, like JSON_PRETTY_PRINT. {@see http://php.net/json.constants}
     * @return string
     */
    public function getDataAsJson(int $options = 0)
    {
        return json_encode($this->data, $options);
    }

    /**
     * Returns data as an array.
     *
     * @param boolean $recursive Force array as the type for all sub-values.
     * @return array The data as an array.
     */
    public function getDataAsArray(bool $recursive = true): array
    {
        if ($recursive)
            return json_decode(json_encode($this->data), true);
        else
            return (array)($this->data);
    }

    /**
     * Returns data as an object.
     *
     * @param boolean $recursive Force object as the type for all sub-values.
     * @return object The data as an object.
     */
    public function getDataAsObject(bool $recursive = true): object
    {
        if ($recursive)
            return json_decode(json_encode($this->data, JSON_FORCE_OBJECT));
        else
            return (object)($this->data);
    }

    /**
     * Gets an index from a data based on the data type.
     *
     * @param string $index The index.
     * @param array|object $data The data to search in.
     * @return mixed Return the value, and if the data is not an array or object or the index does not exists, return null.
     */
    protected function getIndex(string $index, $data)
    {
        if (is_array($data))
            return $data[$index] ?? null;
        if (is_object($data))
            return $data->$index ?? null;
        // If data is neither array nor object
        return null;
    }

    /**
     * Sets an index to a value in a data. 
     *
     * @param string $index The index.
     * @param array|object $data The data to be modified.
     * @param mixed $value The value to be set.
     * @return void
     * @throws \InvalidArgumentException If $data is neither an array nor an object.
     */
    protected function setIndex(string $index, &$data, $value)
    {
        if (is_array($data))
            $data[$index] = $value;
        elseif (is_object($data))
            $data->$index = $value;
        else
            throw new \InvalidArgumentException("Data must be either an array or an object");
    }

    /**
     * Returns value of an index in a data by reference.
     * Returns value of an index in a data by reference. If the index is does not exist, sets it to null before returning.
     *
     * @param string $index The index.
     * @param array|object $data The data to search in.
     * @return mixed The value of the data index by reference.
     * @throws \InvalidArgumentException When data is neither an array nor an object.
     */
    protected function &getIndexByReference(string $index, &$data)
    {
        if (is_array($data)) {
            if (!isset($data[$index]))
                $data[$index] = null;
            return $data[$index];
        } elseif (is_object($data)) {
            if (!isset($data->$index))
                $data->$index = null;
            return $data->$index;
        } else
            throw new \InvalidArgumentException("Wrong data type");
    }

    /**
     * Gets the value of indexes in a data recursively.
     * 
     * @param array $indexes The indexes.
     * @param mixed $data The data to crawl indexes in it.
     * @return mixed The found value of indexes. Returns null if one of indexes cannot be found.
     */
    protected function getIndexesRecursive(array $indexes, $data)
    {
        $indexesCount = count($indexes);
        // The end of recursion, crawling indexes has finished
        if ($indexesCount === 0)
            return $data;
        // Get indexes recursively
        else {
            // Get the current index, and remove it from indexes
            $currentIndex = array_shift($indexes);
            return $this->getIndexesRecursive($indexes, $this->getIndex($currentIndex, $data));
        }
    }

     /**
     * Sets the value of indexes in a data recursively.
     *
     * @param array $indexes The indexes.
     * @param mixed $value The value to set.
     * @param mixed $data The data to crawl indexes in it.
     * @param integer $indexingType The type of the value to create an undefined index.
     * Possible values are: TYPE_ARRAY, TYPE_OBJECT, STRICT_INDEXING
     * @return self
     * @throws \InvalidArgumentException If the generation type is wrong
     * @throws \Exception I
     */
    protected function setIndexesRecursive(
        array $indexes,
        $value,
        &$data,
        int $indexingType = self::TYPE_ARRAY
    ) {
        // Get the current index, and remove it from indexes
        $currentIndex = array_shift($indexes);
        // At the last index, so, setting the value
        if (count($indexes) === 0) {
            $this->setIndex($currentIndex, $data, $value);
            return;
        // Recurse on remained indexes
        } else {
            /*
             * If the current index does not exist, set it to an empty array or an object based on
             * generation type. After making sure that the index exists, change the reference of the
             * data to the data index, for recursion.
             */
            if ($this->getIndex($currentIndex, $data) === null)
                switch ($indexingType) {
                    case self::TYPE_ARRAY:
                        $this->setIndex($currentIndex, $data, array());
                        break;
                    case self::TYPE_OBJECT:
                        $this->setIndex($currentIndex, $data, new \stdClass());
                        break;
                    case self::STRICT_INDEXING:
                        throw new \Exception("Index '$currentIndex' is not defined");
                    default:
                        throw new \InvalidArgumentException("Wrong generation type");
                }

            $data = &$this->getIndexByReference($currentIndex, $data);
            $this->setIndexesRecursive($indexes, $value, $data, $indexingType);
        }

        return $this;
    }

    /**
     * Extract parts of an index into an array divided by the delimiter.
     *
     * @param string $index The index.
     * @param string $delimiter The delimiter.
     * @return array The extracted parts of the index, and an empty array if the index is an empty string.
     */
    protected function extractIndexParts(string $index, string $delimiter = "."): array
    {
        if (empty($index))
            return [];

        // Explode index parts by $delmiter
        return explode($delimiter, $index);
    }

    /**
     * Gets the value of an index in the data.
     *
     * @param string $index The index. It may contain dots for crawling deep indexes (like JavaScript).
     * @return mixed The value of the index. Returns null if the index not found.
     */
    public function get(string $index)
    {
        return $this->getIndexesRecursive($this->extractIndexParts($index), $this->data);
    }

    /**
     * Sets the value to an index in the data.
     *
     * @param string $index The index. It may contain dots for crawling deep indexes (like JavaScript).
     * @param mixed $value The value to be set.
     * @param integer $newIndexType The type of the value to create an undefined index part. It can be either an array (TYPE_ARRAY) or an object (TYPE_OBJECT), or TYPE_STRICT if you want to get exceptions when reaching an undefined index, i.e. all indexes, except the last one, must exist.
     * @return self
     */
    public function set(string $index, $value, int $newIndexType = self::TYPE_ARRAY)
    {
        $delimitedIndex = $this->extractIndexParts($index);
        $this->setIndexesRecursive($delimitedIndex, $value, $this->data, $newIndexType);
        return $this;
    }

    /**
     * Determines if an index exists or not.
     *
     * @param string $index
     * @return boolean
     */
    public function isSet(string $index)
    {
        return $this->get($index) !== null;
    }
    
    /**
     * Iterates over a data index.
     *
     * @param string $index The index.
     * @return iterable
     * @throws \Exception If the value of the data index is not iterable (i.e. neither an array nor an object).
     */
    public function iterate(string $index = ""): iterable
    {
        // Get the value of the index in data
        $data = $this->get($index);   

        if (!(is_array($data) || is_object($data)))
            throw new \Exception("The index is not iterable");

        foreach ((array)$data as $key => $val)
            yield $key => $val;
    }

    /**
     * Gets the JSON data string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getDataAsJson();
    }
}
