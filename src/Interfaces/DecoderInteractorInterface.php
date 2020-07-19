<?php

namespace MAChitgarha\Json\Interfaces;

interface DecoderInteractorInterface
{
    /**
     * Decodes the current data and returns it.
     *
     * @param integer A set of DecodingOption class options.
     * @todo Specify return value: array, object, an instance of a class, or something
     * else? Note that, being an instance of a class might make things easier.
     */
    public function decode(int $options);
}
