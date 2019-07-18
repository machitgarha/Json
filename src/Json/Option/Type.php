<?php
/**
 * @author Mohammad Amin Chitgarha <machitgarha@outlook.com>
 * @see https://github.com/MAChitgarha/JSON
 * @see https://packagist.org/packages/machitgarha/json
 */

namespace MAChitgarha\Json\Option;

class Type
{
    /** @var int Type of the data passed to the constructor. */
    const DEFAULT = 0;
    /** @var int JSON string data type. */
    const JSON_STRING = 1;
    /** @var int Array data type (recursive). */
    const ARRAY = 3;
    /** @var int Object data type (recursive), without converting indexed arrays to objects. */
    const OBJECT = 2;
    /** @var int Object data type (recursive), with converting even indexed arrays to objects. */
    const FULL_OBJECT = 4;    
}
