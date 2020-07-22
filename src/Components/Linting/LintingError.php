<?php

namespace MAChitgarha\Json\Components\Linting;

/**
 * Information of an error happened during linting.
 */
class LintingError
{
    /**
     * The message of the happened error. Could not be null.
     * @var string
     */
    private $message;

    /**
     * Line in which the error happened. Null means no support from the provider.
     * @var ?int
     */
    private $line = null;

    /**
     * Character in which the error happened, in the specified line. Null means no
     * support from the provider.
     * @var ?int
     */
    private $character = null;

    /**
     * Initiailzes a new linting error.
     *
     * @param string $message
     * @param ?int $atLine
     * @param ?int $atChar
     * @return void
     * @todo Specify exceptions and improve their types.
     */
    public function __construct(
        string $message,
        int $atLine = null,
        int $atChar = null
    ) {
        if ($message === "") {
            throw new Exception("Message cannot be empty");
        }
        if ($atLine < 0 || $atChar < 0) {
            throw new Exception("Line and character parameters cannot be negative");
        }

        $this->message = $message;
        $this->line = $atLine;
        $this->character = $atChar;
    }

    /**
     * Return a represntable string of the error.
     *
     * @return string
     */
    public function __toString(): string
    {
        $result = $message;

        if ($this->line !== null) {
            $result .= ", on line {$this->line}";
        }
        if ($this->character !== null) {
            $result .= ", at character {$this->character}";
        }
    }

    /**
     * Returns the error message.
     *
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Returns the line where the error happened.
     *
     * @return int
     */
    public function getLine(): int
    {
        return $this->line;
    }

    /**
     * Returns the character where the error happened, in the specified line.
     *
     * @return int
     */
    public function getCharacter(): int
    {
        return $this->character;
    }
}
