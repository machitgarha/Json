<?php

namespace MAChitgarha\Json\Adapters\Interfaces;

/**
 * Interface for creating adapters providing the operation of linting both JSON strings
 * and built-in PHP data.
 */
interface LinterAdapterInterface extends
    JsonLinterAdapterInterface,
    PhpDataLinterAdapterInterface
{
}
