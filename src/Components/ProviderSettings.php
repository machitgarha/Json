<?php

namespace MAChitgarha\Json\Providers;

use MAChitgarha\Json\Operations\OperationContainer;
use MAChitgarha\Json\Adapters\Interfaces\LinterAdapterInterface;
use MAChitgarha\Json\Adapters\Interfaces\DecoderAdapterInterface;
use MAChitgarha\Json\Adapters\Interfaces\EncoderAdapterInterface;

/**
 * Provider settings for every action, along with adapters.
 *
 * @todo Sort all operations consistently.
 */
class ProviderSettings
{
    /**
     * Default adapter for the provider (if supported).
     * @var string
     */
    public const DEFAULT_ADAPTER = null;

    protected const OPERATION_TO_INTERFACE = [
        OperationContainer::LINTING => LinterAdapterInterface::class,
        OperationContainer::ENCODING => EncoderAdapterInterface::class,
        OperationContainer::DECODING => DecoderAdapterInterface::class,
    ];

    /**
     * Linter class name, providing linting action.
     * @todo Replace operation with action.
     * @var string
     */
    private $linterName;

    /**
     * Name of the class who interacts with the linter.
     * @todo Replace interact with adapt.
     * @var string
     */
    private $linterAdapterName;

    /**
     * Linter adapter instance.
     * @var LinterAdapterInterface
     */
    private $linterAdapter = null;

    /**
     * Encoder class name, providing encoding operation.
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
     * Decoder class name, providing decoding operation.
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
     * Initialize providers and adapter so they can be used.
     *
     * This function should not be called manually.
     *
     * @todo Prevent from calling this method twice.
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
     * Returns the default adapter, based on the provider and the operation.
     *
     * @param string $operationName
     * @param string $providerName
     * @return string
     * @todo Add at-throw.
     */
    protected static function getDefaultAdapter(
        string $operationName,
        string $providerName
    ): string {
        static $providersToDefaultAdaptersMap =
            require __DIR__ . "/../../data/default-adapters.php";

        // TODO: Provider a more detailed exception on what is not supported
        $adapterName = $providersToDefaultAdaptersMap[$providerName][$operationName]
            ?? null;
        if ($adapterName === null) {
            throw new NotSupportedException("There is no default adapter for " .
                "provider $providerName ($operationName operation)");
        }
        return $adapterName;
    }

    /**
     * Returns a valid adapter for the given input.
     *
     * If the adapter is not given (i.e. has been set to self::DEFAULT_ADAPTER), then it
     * returns the default one (if exists). In the case of no default adapter being
     * exist, an exception will be thrown.
     *
     * In both cases, whether the adapter is given or the default one should be chosen,
     * it will be validated to have necessary methods. In other words, it will be forced
     * to implement the related interface of that operation.
     *
     * @param string $operationName
     * @param string $providerName
     * @param string $adapterName
     * @return string
     */
    protected static function getValidAdapter(
        string $operationName,
        string $providerName,
        string $adapterName = self::DEFAULT_ADAPTER
    ): string {
        if ($adapterName === self::DEFAULT_ADAPTER) {
            $adapterName = static::getDefaultAdapter($operationName, $providerName);
        }

        // TODO: Improve the exception message.
        if (!$adapterName instanceof static::OPERATION_TO_INTERFACE[$operationName]) {
            throw new InvalidArgumentException();
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
     * @todo Add at-throw.
     */
    public function setLinter(
        string $linterName,
        string $linterAdapterName = self::DEFAULT_ADAPTER
    ) {
        $this->linterName = $linterName;
        $this->linterAdapterName = static::getValidAdapter(
            OperationContainer::LINTING,
            $linterName,
            $linterAdapterName
        );

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
        string $encoderAdapterName = self::DEFAULT_ADAPTER
    ) {
        $this->encoderName = $encoderName;
        $this->encoderAdapterName = static::getValidAdapter(
            OperationContainer::ENCODING,
            $encoderAdapter,
            $encoderAdapterName
        );

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
        string $decoderAdapterName = self::DEFAULT_ADAPTER
    ) {
        $this->decoderName = $decoderName;
        $this->decoderAdapterName = static::getValidAdapter(
            OperationContainer::DECODING,
            $decoderName,
            $decoderAdapterName
        );

        return $this;
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
