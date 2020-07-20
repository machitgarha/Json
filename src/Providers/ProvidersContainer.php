<?php

namespace MAChitgarha\Json\Providers;

use MAChitgarha\Json\Interfaces\LinterInteractorInterface;

/**
 * Container of providers and their interactors.
 * @todo Decide to move this to Components namespace or not.
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
    private $linterInteractor = null;

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
     * Encoder interactor instance.
     * @var EncoderInteractorInterface
     */
    private $encoderInteractor = null;

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

    /**
     * Decoder interactor instance.
     * @var DecoderInteractorInterface
     */
    private $decoderInteractor = null;

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

    /**
     * Initialize providers and interactor so they can be used.
     *
     * @param mixed $data
     * @return void
     */
    public function init($data)
    {
        $distinctProviders = [];

        foreach ([
            [
                $this->linterName,
                $this->linterInteractorName,
                $this->linterInteractor
            ],
            [
                $this->encoderName,
                $this->encoderInteractorName,
                $this->encoderInteractor
            ],
            [
                $this->decoderName,
                $this->decoderInteractorName,
                $this->decoderInteractor
            ],
        ] as list(
            $providerName,
            $interactorName,
            &$interactorRef
        )) {
            if ($distinctProviders[$providerName] === null) {
                $distinctProviders[$providerName] =
                    new $interactorName($providerName, $data);
            }
            $interactorRef = $distinctProviders[$interactorName];
        }
    }

    /**
     * Returns linter interactor.
     *
     * @return LinterInteractorInterface
     */
    public function getLinterInteractor(): LinterInteractorInterface
    {
        return $this->linterInteractor;
    }

    /**
     * Returns encoder interactor.
     *
     * @return EncoderInteractorInterface
     */
    public function getEncoderInteractor(): EncoderInteractorInterface
    {
        return $this->encoderInteractor;
    }

    /**
     * Returns decoder interactor.
     *
     * @return DecoderInteractorInterface
     */
    public function getDecoderInteractor(): DecoderInteractorInterface
    {
        return $this->decoderInteractorName;
    }
}
