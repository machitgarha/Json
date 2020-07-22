<?php
/**
 * @author Mohammad Amin Chitgarha <machitgarha@outlook.com>
 * @see https://github.com/MAChitgarha/Json
 * @see https://packagist.org/packages/machitgarha/json
 */

namespace MAChitgarha\Json\Options;

/**
 * Options to be passed to Json::__construct().
 * @todo Move these things as options in EncodingOption and DecodingOption.
 */
class JsonOpt
{
    /**
     * @var int Check every string value if it is a valid JSON string, and then decode it and use
     * the decoded value instead of the string itself. However, if a string does not contain a valid
     * JSON string, then use the string itself. This option affects performance in some cases, but
     * it would be so much useful if you work with JSON strings a lot.
     */
    const DECODE_ALWAYS = 1;
}
