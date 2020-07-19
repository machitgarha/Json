<?php
/**
 * @author Mohammad Amin Chitgarha <machitgarha@outlook.com>
 * @see https://github.com/MAChitgarha/Json
 * @see https://packagist.org/packages/machitgarha/json
 */

namespace MAChitgarha\Json\Configurations;

class DoOpt
{
    /**
     * @var int Use index as a key, and don't extract it into one or more keys using dots. For
     * example, by default, require.php will be extracted into two key: require and php; then they
     * will be found in data recursively. However, with this option set, it will be considered as
     * one key (i.e. require.php key will be found in data).
     */
    const KEEP_INDEX = 1;
}
