<?php

namespace MAChitgarha\Json\Providers;

use MAChitgarha\Json\Interfaces\LinterInteractorInterface;

/**
 * Container of providers and their interactors.
 */
class ProvidersContainer
{
    /**
     * Auto-detect the interactor of the provider.
     * @todo Implement this.
     * @var string
     */
    public const AUTO_DETECT = "";

    /**
     * Linter class name, providing linting functionality.
     * @var string
     */
    private $linterName;

    /**
     * Name of the class who interacts with the linter.
     * @var string
     */
    private $linterInteractorName;

    /**
     * Linter interactor instance.
     * @var LinterInteractorInterface
     */
    private $linterInteractor;

    /**
     * Encoder class name, providing encoding functionality.
     * @var string
     */
    private $encoderName;

    /**
     * Name of the class who interacts with the encoder.
     * @var string
     */
    private $encoderInteractorName;

    /**
     * Decoder class name, providing decoding functionality.
     * @var string
     */
    private $decoderName;

    /**
     * Name of the class who interacts with the decoder.
     * @var string
     */
    private $decoderInteractorName;

    public function __construct()
    {
    }

    /**
     * Returns new object from the class.
     *
     * @return self
     */
    public static function new()
    {
        return new static();
    }

    /**
     * Sets the linter provider and its interactor.
     *
     * If the interactor is not specified, it will be auto-detected.
     *
     * @param string $linterName
     * @param string $linterInteractorName
     * @return self
     * @todo Add at-throw when auto-detection failed or class not supported for this
     * method and the methods likewise.
     */
    public function setLinter(
        string $linterName,
        string $linterInteractorName = self::AUTO_DETECT
    ) {
        $this->linterName = $linterName;
        $this->linterInteractorName = $linterInteractorName;

        return $this;
    }

    /**
     * Sets the encoder provider and its interactor.
     *
     * If the interactor is not specified, it will be auto-detected.
     *
     * @param string $encoderName
     * @param string $encoderInteractorName
     * @return self
     */
    public function setEncoder(
        string $encoderName,
        string $encoderInteractorName = self::AUTO_DETECT
    ) {
        $this->encoderName = $encoderName;
        $this->encoderInteractorName = $encoderInteractorName;

        return $this;
    }

    /**
     * Sets the decoder provider and its interactor.
     *
     * If the interactor is not specified, it will be auto-detected.
     *
     * @param string $decoderName
     * @param string $decoderInteractorName
     * @return self
     */
    public function setDecoder(
        string $decoderName,
        string $decoderInteractorName = self::AUTO_DETECT
    ) {
        $this->decoderName = $decoderName;
        $this->decoderInteractorName = $decoderInteractorName;

        return $this;
    }
}
