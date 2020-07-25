<?php

namespace MAChitgarha\Json\Adapters;

use MAChitgarha\Json\Adapters\Interfaces\LinterAdapterInterface;
use MAChitgarha\Json\Adapters\Interfaces\DecoderAdapterInterface;
use MAChitgarha\Json\Adapters\Interfaces\EncoderAdapterInterface;
use MAChitgarha\Json\Components\Data;

/**
 * Adapter provding encoding and decoding.
 */
class StEncDecAdapter implements
    DecoderAdapterInterface,
    EncoderAdapterInterface
{
    /**
     * Provider class name.
     * @var string
     */
    private $className;

    /**
     * @inheritDoc
     */
    public function __construct(string $className, Data &$data)
    {
        $this->className = $className;
    }

    /**
     * @inheritDoc
     */
    public function decode(string $data, int $options)
    {
        return $this->className::decode($data, $options);
    }

    /**
     * @inheritDoc
     */
    public function encode($data, int $options)
    {
        return $this->className::encode($data, $options);
    }
}
