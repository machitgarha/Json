<?php
/**
 * @author Mohammad Amin Chitgarha <machitgarha@outlook.com>
 * @see https://github.com/MAChitgarha/Json
 * @see https://packagist.org/packages/machitgarha/json
 */

namespace MAChitgarha\Json\Option;

class Indexing
{
    /**
     * @var int If a key cannot be found, create it, and if a key contains an uncountable value,
     * override it. It could cause data loss, as it replaces everything.
     */
    const FREE = 0;
    /**
     * @var int If a key cannot be found, create it, and if a key contains an uncountable value,
     * don't continue and throw an exception. In other words, indexing will only be continued if all
     * keys contain countable values or are not defined.
     */
    const SAFE = 1;
    /**
     * @var int If a key cannot be found, or a key contains an uncountable value, don't continue
     * and throw an exception. As a result, indexing will only be continued if all keys contain
     * countable values. It would be really useful when you want to get the element's value, for
     * example.
     */
    const STRICT = 2;
}
