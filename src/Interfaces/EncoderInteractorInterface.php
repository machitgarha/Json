<?php

namespace MAChitgarha\Json\Interfaces;

interface EncoderInteractorInterface
{
    /**
     * Encodes the current data to JSON.
     *
     * @param integer $options A set of EncodingOption class options.
     * @return string The encoded data as JSON.
     */
    public function encode(int $options): string;
}
