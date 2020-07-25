<?php

namespace MAChitgarha\Json\Interfaces;

interface EncoderAdapterInterface extends BaseAdapterInterface
{
    /**
     * Encodes the given data to JSON string.
     *
     * @todo Provide JSON depth limit support (also for Decoding counterpart).
     * @param mixed $data Data to be encoded.
     * @param int $options A set of EncodingOption class options.
     * @return string The encoded data as JSON.
     * @todo Provide at-throws for this (and for Decoding).
     */
    public function encode($data, int $options): string;
}
