<?php
// TODO: Add a script for adding boilerprait for each file does not have it.

namespace MAChitgarha\Json\Interfaces;

interface BaseInteractorInterface
{
    /**
     * Constructs the data needed for interacting with the underlying class.
     *
     * @todo Complete this documentation.
     * @param string $className
     * @param Data &$data
     * @todo Add options parameter, also for all children.
     * @return void
     */
    public function __construct(string $className, Data &$data);
}
