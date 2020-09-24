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
     * The provider class name.
     * @var string
     */
    private $providerClass;

    /**
     * @inheritDoc
     */
    public function __construct(string $providerClass, Data &$data)
    {
        $this->providerClass = $providerClass;
    }

    /**
     * @inheritDoc
     */
    public function decode(string $data, int $options)
    {
        return $this->providerClass::decode($data, $options);
    }

    /**
     * @inheritDoc
     */
    public function encode($data, int $options)
    {
        return $this->providerClass::encode($data, $options);
    }
}
