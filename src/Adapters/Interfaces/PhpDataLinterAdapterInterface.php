<?php

namespace MAChitgarha\Json\Adapters\Interfaces;

/**
 * Interface for creating adapters providing the operation of linting built-in PHP data.
 */
interface PhpDataLinterAdapterInterface extends BaseAdapterInterface
{
    /**
     * Lints built-in PHP data not to contain not-JSON-encodable data inside.
     *
     * An example of invalid PHP data type that cannot be encoded into JSON data could be
     * resource.
     *
     * @param mixed $data Built-in PHP data to be linted.
     * @todo Specify the return value type.
     */
    public function lintPhpData($data, int $options);
}
