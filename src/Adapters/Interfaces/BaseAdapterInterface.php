<?php
// TODO: Add a script for adding boilerprait for each file does not have it.

namespace MAChitgarha\Json\Adapters\Interfaces;

/**
 * Base interface for creating adapters (and other adapter interfaces).
 *
 * All other interfaces must extend from this interface (either directly or indirectly).
 * Adapters must implement this interface indirectly, by other interfaces.
 */
interface BaseAdapterInterface
{
    /**
     * Constructor.
     *
     * The adapter should not have a list of supported providers, and check the given
     * provider againts them. There are two main reasons for this. First, it's the sole
     * responsibility of the caller to pass a valid supported provider. In other words,
     * either the default adapter should be chosen properly (by the handler), or the user
     * should pass the suitable adapter based on the provider. Second, all adapters
     * should be forward-comptabile to new providers that has the proper interface the
     * adapter supports.
     *
     * @param string $providerClass The provider class name.
     * @return void
     *
     * @todo Complete this documentation.
     * @todo Add options parameter, also for all children.
     * @todo Specfiy which exceptions should be thrown here and in child classes.
     */
    public function __construct(string $providerClass);
}
