<?php

namespace MAChitgarha\Json\Interfaces;

interface DecoderInteractorInterface extends BaseInteractorInterface
{
    /**
     * Decodes the given data and returns it.
     *
     * @param string $data The JSON string to be decoded.
     * @param int $options A set of DecodingOption class options.
     * @return mixed
     */
    public function decode(string $data, int $options);
}
