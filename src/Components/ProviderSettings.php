<?php

namespace MAChitgarha\Json\Components;

use MAChitgarha\Json\Operations\OperationContainer;
use MAChitgarha\Json\Adapters\Interfaces\BaseAdapterInterface;
use MAChitgarha\Json\Adapters\Interfaces\DecoderAdapterInterface;
use MAChitgarha\Json\Adapters\Interfaces\EncoderAdapterInterface;
use MAChitgarha\Json\Adapters\Interfaces\JsonLinterAdapterInterface;
use MAChitgarha\Json\Utils\AdapterUtils;

/**
 * Provider (and adapter) settings holder.
 *
 * For each operation, you can choose a different provider to be used. Also, for each
 * provider, you can choose an adapter manually, or you can let the class to select the
 * default proper adapter for your provider (if supported). To know about the supported
 * providers, refer to default-adapters (PHP) file.
 *
 * If you only create an instance of the current class and do not set any providers (e.g.
 * you only care about things to work properly, you do not care about what provider offer
 * better performance), then the default ones are being used.
 *
 * @todo Write what the class do and WHAT NOT.
 */
class ProviderSettings
{
    /*
     * Properties to hold provider information for each operation. See _ProviderInfo class
     * below for more information.
     */
    // TODO: Add default providers and adapters for each one
    // {
    /** @var _ProviderInfo */
    private $decoderInfo = (object)[
        _ProviderInfo::providerClass => null,
        _ProviderInfo::adapterClass => null,
    ];

    /** @var _ProviderInfo */
    private $encoderInfo = (object)[
        _ProviderInfo::providerClass => null,
        _ProviderInfo::adapterClass => null,
    ];

    /** @var _ProviderInfo */
    private $jsonLinterInfo = (object)[
        _ProviderInfo::providerClass => null,
        _ProviderInfo::adapterClass => null,
    ];
    // }

    /**
     * Constructor.
     */
    public function __construct()
    {
    }

    /**
     * Returns a new instance of the class.
     *
     * @see self::__construct()
     * @param mixed $args Arguemnts forwarded to the constructor.
     * @return self
     */
    public static function new(...$args): self
    {
        return new static(...$args);
    }

    /**
     * Checks the operation, provider and adapter
     * @param string $operationName [description]
     * @param string $providerClass [description]
     * @param string $adapterClass The adapter class name. You may leave
     * @return void
     */
    private static function validate(
        string $operationName,
        string $providerClass,
        ?string $adapterClass = AdapterUtils::DEFAULT_ADAPTER
    ): void {
        ValidationUtils::ensureClassExists($providerClass, "provider");

        // TODO: Document this maybe?
        if ($adapterClass !== AdapterUtils::DEFAULT_ADAPTER) {
            ValidationUtils::ensureClassExists($adapterClass, "adapter");
            ValidationUtils::ensureImplements(
                $adapterClass,
                AdapterUtils::getAdapterInterfaceFromOperation($operationName)
            );
        }
    }

    /**
     * Returns a provider information instance, if given input is valid.
     *
     * @param string $operationName The operation name.
     * @param string $providerClass The provider class name.
     * @param string $adapterClass The adapter class name.
     * @return _ProviderInfo
     */
    protected static function getValidProviderInfo(
        string $operationName,
        string $providerClass,
        string $adapterClass = AdapterUtils::DEFAULT_ADAPTER
    ): _ProviderInfo {
        // Selecting default adapter class, if not provided
        if ($adapterClass === AdapterUtils::DEFAULT_ADAPTER) {
            $adapterClass = AdapterUtils::getDefaultAdapterClass(
                $operationName,
                $providerClass
            );
        }

        // Throws exception if bad things happen
        self::validateProviderInfo($operationName, $providerClass, $adapterClass);

        // Everything is valid now
        $validProviderInfo = new _ProviderInfo();
        $validProviderInfo->providerClass = $providerClass;
        $validProviderInfo->adapterClass = $adapterClass;
        return $validProviderInfo;
    }

    /**
     * Sets provider for JSON linting operation; and the adapter, if needed.
     *
     * @param string $providerClass The provider class name.
     * @param string $adapterClass The adapter class name.
     * @return self
     */
    public function setJsonLinter(
        string $providerClass,
        string $adapterClass = AdapterUtils::DEFAULT_ADAPTER
    ): self {
        $this->jsonLinterInfo = static::getValidProviderInfo(
            OperationContainer::JSON_LINTING,
            $providerClass,
            $adapterClass
        );

        return $this;
    }

    /**
     * Sets the encoder provider and its adapter.
     *
     * @param string $providerClass The provider class name.
     * @param string $adapterClass The adapter class name.
     * @return self
     */
    public function setEncoder(
        string $providerClass,
        string $adapterClass = AdapterUtils::DEFAULT_ADAPTER
    ): self {
        $this->encoderInfo = static::getValidProviderInfo(
            OperationContainer::JSON_LINTING,
            $providerClass,
            $adapterClass
        );

        return $this;
    }

    /**
     * Sets the decoder provider and its adapter.
     *
     * @param string $providerClass The provider class name.
     * @param string $adapterClass The adapter class name.
     * @return self
     */
    public function setDecoder(
        string $providerClass,
        string $adapterClass = AdapterUtils::DEFAULT_ADAPTER
    ): self {
        $this->decoderInfo = static::getValidProviderInfo(
            OperationContainer::JSON_LINTING,
            $providerClass,
            $adapterClass
        );

        return $this;
    }
}

/**
 * A (private) class to hold information of a provider.
 *
 * @todo Maybe better documentation for this?
 */
class _ProviderInfo
{
    /*
     * Names of properties available in the class, as constants.
     */
    public const providerClass = "providerClass";
    public const adapterClass = "adapterClass";

    /**
     * The provider class name.
     * @var ?string
     */
    public $providerClass;

    /**
     * The adapter class name.
     * @var ?string
     */
    public $adapterClass;
}
