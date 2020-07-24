<?php

namespace MAChitgarha\Json\Interactors;

use MAChitgarha\Json\Interfaces\LinterInteractorInterface;
use MAChitgarha\Json\Interfaces\DecoderInteractorInterface;
use MAChitgarha\Json\Interfaces\EncoderInteractorInterface;
use MAChitgarha\Json\Components\Data;

/**
 * Interactor provding encoding and decoding.
 */
class StEncDecInteractor implements
    DecoderInteractorInterface,
    EncoderInteractorInterface
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
