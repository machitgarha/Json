<?php

namespace MAChitgarha\Json\Components\Linting;

/**
 * Represents a linting result.
 * @todo Support for warnings.
 */
class LintingResult
{
    /**
     * The output when linting was successful.
     * @see self::__toString()
     * @var string
     */
    public const SUCCESSFUL_LINTING_OUTPUT = "Linting was successful";

    /**
     * An array of happened errors.
     * @var LintingError[]
     */
    public $errors = [];

    /**
     * Constructs a linting result.
     */
    public function __construct()
    {
    }

    /**
     * Adds a new error to linting result.
     *
     * @param Error $error The detected error.
     * @return self
     */
    public function addError(Error $error)
    {
        $this->errors[] = $error;

        return $this;
    }

    /**
     * Tells whether any errors detected during linting or not.
     *
     * @return bool
     */
    public function hasError(): bool
    {
        return count($this->errors) !== 0;
    }

    /**
     * Tells whether the linting was completed without any errors or not.
     *
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return count($this->errors) === 0;
    }

    /**
     * Returns all happened error.
     *
     * @return array
     */
    public function getAllErrors(): array
    {
        return $this->errors;
    }

    /**
     * Returns errors one by one, using a Generator.
     *
     * @return Generator
     */
    public function getError(): \Generator
    {
        foreach ($this->errors as $error) {
            yield $error;
        }
    }

    /**
     * Get the first happened error.
     *
     * @return ?Error If no errors exist, returns null.
     */
    public function getFirstError(): ?Error
    {
        return $this->errors[0] ?? null;
    }

    /**
     * Represent the error as string.
     *
     * @return string If linting is successful, provide a simple message representing it.
     */
    public function __toString()
    {
    }
}
