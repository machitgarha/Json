<?php

namespace MAChitgarha\Json\Interfaces;

use MAChitgarha\Json\Components\Linting\LintingResult;

interface LinterInteractorInterface
{
    /**
     * Lints the existing data.
     *
     * @return LintingResult
     */
    public function lint(): LintingResult;
}
