<?php

namespace MAChitgarha\Json\Components\Linting;

/**
 * Represents a linting result.
 * @todo Support for warnings.
 * @todo Add an interface for this.
 */
class LintingResult
{
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
     * @param LintingError $error The detected error.
     * @return self
     */
    public function addError(LintingError $error)
    {
        $this->errors[] = $error;

        return $this;
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
     * @return ?LintingError If no errors exist, returns null.
     */
    public function getFirstError(): ?LintingError
    {
        return $this->errors[0] ?? null;
    }

    /**
     * Returns the count of the happened errors.
     *
     * @return int
     */
    public function getErrorCount(): int
    {
        return count($this->errors);
    }

    /**
     * Tells whether any errors detected during linting or not.
     *
     * @return bool
     */
    public function hasError(): bool
    {
        return $this->getErrorCount() !== 0;
    }

    /**
     * Tells whether the linting was completed without any errors or not.
     *
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return $this->getErrorCount() === 0;
    }
}
