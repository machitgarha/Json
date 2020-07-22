<?php

namespace MAChitgarha\Json\Interfaces;

use MAChitgarha\Json\Components\Linting\LintingResult;

interface LinterInteractorInterface extends BaseInteractorInterface
{
    /**
     * Lints the existing data.
     *
     * @return LintingResult
     */
    public function lint(): LintingResult;
}
