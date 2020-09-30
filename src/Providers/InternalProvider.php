<?php

namespace MAChitgarha\Json\Providers;

use MAChitgarha\Json\Exceptions\JsonException;
use MAChitgarha\Json\Components\Linting\LintingError;
use MAChitgarha\Json\Components\Linting\LintingResult;

/**
 * A class providing interface to PHP internal JSON capabilities (e.g. json_encode()).
 */
class InternalProvider
{
    /**
     * Default depth of the encoding/decoding process.
     * @var int
     */
    protected const DEFAULT_DEPTH = 512;

    /**
     * Encodes the given data.
     *
     * @param mixed $data The data to be encoded.
     * @param int $options See PHP documentation for available options.
     * @return string The encoded JSON string.
     */
    public static function encode($data, int $options = 0): string
    {
        $encodedData = json_encode($data, $options, static::DEFAULT_DEPTH);
        self::handleJsonErrors(json_last_error());
        return $encodedData;
    }

    /**
     * Decodes the given JSON string.
     *
     * @param string $data The data to be decoded.
     * @param int $options See PHP documentation for available options.
     * @return mixed
     */
    public static function decode(string $data, int $options = 0)
    {
        $decodedData = json_decode($data, true, static::DEFAULT_DEPTH, $options);
        self::handleJsonErrors(json_last_error());
        return $decodedData;
    }

    /**
     * Handles JSON errors and throw exceptions, if needed.
     *
     * @param int $jsonErrorStat The return value of json_last_error().
     * @return void
     */
    protected static function handleJsonErrors(int $jsonErrorStat)
    {
        switch ($jsonErrorStat) {
            case JSON_ERROR_NONE:
                // No error happened
                return;

            case JSON_ERROR_DEPTH:
                $message = "Maximum stack depth exceeded";
                break;

            case JSON_ERROR_STATE_MISMATCH:
            case JSON_ERROR_SYNTAX:
                $message = "Invalid or malformed JSON";
                break;

            case JSON_ERROR_CTRL_CHAR:
            case JSON_ERROR_UTF8:
            case JSON_ERROR_UTF16:
                $message = "Malformed characters, possibly incorrectly encoded JSON";
                break;

            case JSON_ERROR_INF_OR_NAN:
                $message = "NAN and INF cannot be encoded";
                break;

            case JSON_ERROR_INVALID_PROPERTY_NAME:
                $message = "Found an invalid property name";
                break;

            case JSON_ERROR_UNSUPPORTED_TYPE:
                $message = "A value cannot be encoded, possibly a resource";
                break;

            default:
                $message = "Unknown JSON error";
                break;
        }

        throw new JsonException($message, $jsonErrorStat);
    }

    /**
     * Lints the given JSON data.
     *
     * @param string $data The JSON data to be linted.
     * @todo Specify return value.
     */
    public static function lint(string $data): LintingResult
    {
        $result = new Linting\LintingResult();

        try {
            static::encode($data);
        } catch (JsonException $e) {
            $result->addError(new LintingError(
                $e->getMessage()
            ));
        }

        return $result;
    }
}
