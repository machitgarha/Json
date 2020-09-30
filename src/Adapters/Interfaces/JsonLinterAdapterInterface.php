<?php

namespace MAChitgarha\Json\Adapters\Interfaces;

use MAChitgarha\Json\Components\Linting\LintingResult;

/**
 * Interface for creating adapters providing the operation of linting JSON strings.
 */
interface JsonLinterAdapterInterface extends BaseAdapterInterface
{
    /**
     * Lints a JSON string.
     *
     * @param string $data
     * @param int $options
     * @return LintingResult The result of the lint. May include errors.
     */
    public function lintJson(string $data, int $options): LintingResult;
}
