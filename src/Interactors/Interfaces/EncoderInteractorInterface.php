<?php

namespace MAChitgarha\Json\Interfaces;

interface EncoderInteractorInterface
{
    /**
     * Encodes the current data to JSON.
     *
     * @todo Provide JSON depth limit support (also for Decoding counterpart).
     * @param integer $options A set of EncodingOption class options.
     * @return string The encoded data as JSON.
     * @todo Provide at-throws for this (and for Decoding).
     */
    public function encode(int $options): string;
}
