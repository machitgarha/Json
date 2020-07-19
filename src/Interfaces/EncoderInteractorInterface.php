<?php

namespace MAChitgarha\Json\Interfaces;

interface EncoderInteractorInterface
{
    /**
     * Encodes the current data to JSON.
     *
     * @todo Add options parameter.
     * @return string The encoded data as JSON.
     */
    public function encode(): string;
}
