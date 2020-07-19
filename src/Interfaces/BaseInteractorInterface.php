<?php
// TODO: Add a script for adding boilerprait for each file does not have it.

namespace MAChitgarha\Interfaces;

interface BaseInteractorInterface
{
    /**
     * Initializes the handler to be used in other interacting methods, and returns it.
     *
     * @todo Complete this documentation.
     * @param string $className
     * @param mixed $data
     * @return string|object The handler.
     */
    public static function init(string $className, $data);
}
