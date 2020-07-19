<?php
/**
 * @author Mohammad Amin Chitgarha <machitgarha@outlook.com>
 * @see https://github.com/MAChitgarha/Json
 * @see https://packagist.org/packages/machitgarha/json
 */

namespace MAChitgarha\Json\Configurations;

class Merge
{
    /**
     * @var int When merging two elements with the same key, keep the value of default data instead
     * of replacing it with the passed data.
     */
    const KEEP_DEFAULT = 1;
}
