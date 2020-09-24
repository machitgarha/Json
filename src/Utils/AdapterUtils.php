<?php

namespace MAChitgarha\Json\Utils;

use MAChitgarha\Json\Exceptions\NotSupportedException;

/**
 * Utilities for adapters.
 */
class AdapterUtils
{
    /**
     * Use the default adapter (for the given input), if supported.
     * @var null
     */
    public const DEFAULT_ADAPTER = null;

    /**
     * Path to the file default adapters data live.
     * @var string
     */
    protected const DEFAULT_ADAPTERS_DATA_PATH =
        __DIR__ . "/../../data/default-adapters.php";

    /**
     * Default adapters data. Until getting the data from the file, the value is null.
     * @var ?array
     */
    private static $defaultAdaptersData = null;

    /**
     * Returns default adapters data.
     *
     * @return array
     */
    protected static function getDefaultAdaptersData(): array
    {
        if (static::$defaultAdaptersData === null) {
            static::$defaultAdaptersData = require static::_DEFAULT_ADAPTERS_DATA_PATH;
        }
        return static::$defaultAdaptersData;
    }

    /**
     * Returns the default adapter class name, based on the provider and the operation.
     *
     * @param string $operationName The operation name.
     * @param string $providerClass The provider class name.
     * @return string
     * @throws NotSupportedException If no default adapter exist.
     */
    protected static function getDefaultAdapterName(
        string $operationName,
        string $providerClass
    ): string {
        $defaultAdaptersData = static::getDefaultAdaptersData();

        if (isset($defaultAdaptersData[$providerClass])) {
            throw new NotSupportedException(
                "No default adapter data exist for '$providerClass' provider class"
            );
        }
        if (isset($defaultAdaptersData[$providerClass][$operationName])) {
            throw new NotSupportedException(
                "No default adapter set for '$providerClass' provider class " .
                "to do $operationName operation"
            );
        }

        return $defaultAdaptersData[$providerClass][$operationName];
    }

    /**
     * Returns a valid adapter for the given input.
     *
     * If the adapter is not given (i.e. has been set to self::DEFAULT_ADAPTER), then it
     * returns the default one (if exists). In the case of no default adapter being
     * exist, an exception will be thrown.
     *
     * @param string $operationName The operation name.
     * @param string $providerClass The provider class name.
     * @param string $providerClass The base adapter interface name, in which
     * the adapter should be an instance of.
     * @param string $providerClass The adapter name.
     * @return string
     */
    public static function getValidAdapterName(
        string $operationName,
        string $providerClass,
        string $providerClass,
        string $providerClass = self::DEFAULT_ADAPTER
    ): string {
        if ($providerClass === self::DEFAULT_ADAPTER) {
            $providerClass = static::getDefaultAdapterName($operationName, $providerClass);
        }

        if (!$providerClass instanceof $providerClass) {
            throw new InvalidArgumentException(
                "Adapter '$providerClass' must implement '$providerClass' " .
                "to be used as $operationName operation"
            );
        }

        return $providerClass;
    }
}
