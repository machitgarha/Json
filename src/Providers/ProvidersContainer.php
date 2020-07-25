<?php

namespace MAChitgarha\Json\Providers;

use MAChitgarha\Json\Interfaces\LinterAdapterInterface;

/**
 * Container of providers and their adapters.
 * @todo Decide to move this to Components namespace or not.
 */
class ProvidersContainer
{
    /**
     * Auto-detect the adapter.
     * @var string
     */
    public const AUTO_DETECT = null;

    /**
     * Linter class name, providing linting functionality.
     * @var string
     */
    private $linterName;

    /**
     * Name of the class who interacts with the linter.
     * @var string
     */
    private $linterAdapterName;

    /**
     * Linter adapter instance.
     * @var LinterAdapterInterface
     */
    private $linterAdapter = null;

    /**
     * Encoder class name, providing encoding functionality.
     * @var string
     */
    private $encoderName;

    /**
     * Name of the class who interacts with the encoder.
     * @var string
     */
    private $encoderAdapterName;

    /**
     * Encoder adapter instance.
     * @var EncoderAdapterInterface
     */
    private $encoderAdapter = null;

    /**
     * Decoder class name, providing decoding functionality.
     * @var string
     */
    private $decoderName;

    /**
     * Name of the class who interacts with the decoder.
     * @var string
     */
    private $decoderAdapterName;

    /**
     * Decoder adapter instance.
     * @var DecoderAdapterInterface
     */
    private $decoderAdapter = null;

    /**
     * Constructor.
     */
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
     * Returns the auto-detected adapter based on provider name and functionality.
     *
     * @param string $functionality Functionality to provide (e.g. linting).
     * @param string $providerName Provider full class name.
     * @return string The detected adapter name.
     */
    protected static function autoDetect(
        string $functionality,
        string $providerName,
        string $adapterName = self::AUTO_DETECT
    ): string {
        // If adapter name is provided, return that
        if ($adapterName !== self::AUTO_DETECT) {
            return $adapterName;
        }
        // Otherwise, auto-detect it

        static $autoDetectionData =
            require __DIR__ . "/../../data/adapter-auto-detection.php";

        $adapterName = $autoDetectionData[$providerName][$functionality] ?? null;

        if ($adapterName === null) {
            throw new NotSupportedException("Functionality $functionality for provider " .
                "$providerName cannot be auto-detected");
        }
        return $adapterName;
    }

    /**
     * Sets the linter provider and its adapter.
     *
     * If the adapter is not specified, it will be auto-detected.
     *
     * @param string $linterName
     * @param string $linterAdapterName
     * @return self
     * @todo Add at-throw when auto-detection failed or class not supported for this
     * method and the methods likewise.
     */
    public function setLinter(
        string $linterName,
        string $linterAdapterName = self::AUTO_DETECT
    ) {
        $this->linterName = $linterName;
        $this->linterAdapterName = static::autoDetect(
            "linting",
            $linterName,
            $linterAdapterName
        );

        if (!$linterAdapterName instanceof LinterAdapterInterface) {
            throw new Exception();
        }

        return $this;
    }

    /**
     * Sets the encoder provider and its adapter.
     *
     * If the adapter is not specified, it will be auto-detected.
     *
     * @param string $encoderName
     * @param string $encoderAdapterName
     * @return self
     */
    public function setEncoder(
        string $encoderName,
        string $encoderAdapterName = self::AUTO_DETECT
    ) {
        $this->encoderName = $encoderName;
        $this->encoderAdapterName = $encoderAdapterName;

        return $this;
    }

    /**
     * Sets the decoder provider and its adapter.
     *
     * If the adapter is not specified, it will be auto-detected.
     *
     * @param string $decoderName
     * @param string $decoderAdapterName
     * @return self
     */
    public function setDecoder(
        string $decoderName,
        string $decoderAdapterName = self::AUTO_DETECT
    ) {
        $this->decoderName = $decoderName;
        $this->decoderAdapterName = $decoderAdapterName;

        return $this;
    }

    /**
     * Initialize providers and adapter so they can be used.
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
                $this->linterAdapterName,
                $this->linterAdapter
            ],
            [
                $this->encoderName,
                $this->encoderAdapterName,
                $this->encoderAdapter
            ],
            [
                $this->decoderName,
                $this->decoderAdapterName,
                $this->decoderAdapter
            ],
        ] as list(
            $providerName,
            $adapterName,
            &$adapterRef
        )) {
            if ($distinctProviders[$providerName] === null) {
                $distinctProviders[$providerName] =
                    new $adapterName($providerName, $data);
            }
            $adapterRef = $distinctProviders[$adapterName];
        }
    }

    /**
     * Returns linter adapter.
     *
     * @return LinterAdapterInterface
     */
    public function getLinterAdapter(): LinterAdapterInterface
    {
        return $this->linterAdapter;
    }

    /**
     * Returns encoder adapter.
     *
     * @return EncoderAdapterInterface
     */
    public function getEncoderAdapter(): EncoderAdapterInterface
    {
        return $this->encoderAdapter;
    }

    /**
     * Returns decoder adapter.
     *
     * @return DecoderAdapterInterface
     */
    public function getDecoderAdapter(): DecoderAdapterInterface
    {
        return $this->decoderAdapter;
    }
}
