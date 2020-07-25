<?php

namespace MAChitgarha\Json\Adapters\Interfaces;

use MAChitgarha\Json\Components\Linting\LintingResult;

interface LinterAdapterInterface extends BaseAdapterInterface
{
    /**
     * Lints a JSON string.
     *
     * @param string $data A JSON string.
     * @return LintingResult
     */
    public function lintJsonString(string $data): LintingResult;

    /**
     * Lints a ordinary PHP data not to contain non-JSON-encodable data inside.
     *
     * As an example, an invalid ordinary PHP type to be encoded is a resource.
     *
     * You should throw exceptions or return true from this method, if the provider don't
     * support linting plain data.
     *
     * @param mixed $data Plain PHP data.
     * @todo Specify the return value type.
     */
    public function lintOrdinaryData($data);
}
