<?php
// TODO: Add a script for adding boilerprait for each file does not have it.

namespace MAChitgarha\Interfaces;

interface BaseInteractorInterface
{
    /**
     * Constructs the data needed for interacting with the underlying class.
     *
     * @todo Complete this documentation.
     * @param string $className
     * @param mixed $data
     * @todo Add options parameter.
     * @return void
     */
    public function __construct(string $className, $data);
}
