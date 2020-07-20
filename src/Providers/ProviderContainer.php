<?php

namespace MAChitgarha\Json\Providers;

/**
 * Container for feature providers and their interactors.
 */
class ProviderContainer
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

    public function setLinter(
        string $linterName,
        string $linterInteractorName = self::AUTO_DETECT
    ) {
    }

    public function setEncoder(
        string $encoderName,
        string $encoderInteractorName = self::AUTO_DETECT
    ) {
    }

    public function setDecoder(
        string $decoderName,
        string $decoderInteractorName = self::AUTO_DETECT
    ) {
    }
}
