<?php
/**
 * @author Mohammad Amin Chitgarha <machitgarha@outlook.com>
 * @see https://github.com/MAChitgarha/JSON
 * @see https://packagist.org/packages/machitgarha/json
 */

namespace MAChitgarha\Json\Option;

/**
 * Options to be passed to Json::__construct().
 */
class JsonOpt
{
    /**
     * @var int Check every string value to be a valid JSON string, and if it is, decode it and use
     * the decoded value instead of the string itself. However, if a string does not contain a valid
     * JSON string, then use the string itself. This option affects performance in some cases, but
     * it would be so much useful if you work with JSON strings a lot.
     */
    const DECODE_ALWAYS = 1;
    /**
     * @var int Consider data passed into the constructor as string, even if it's a valid JSON data;
     * in other words, don't decode it. Using this option, every non-string and uncountable values
     * will be converted to a string (i.e. integers, booleans and NULL). This option only affects
     * the constructor. It won't have any effects if you use it in combination with
     * JSON::OPT_TREAT_AS_JSON_STRING.
     */
    const AS_STRING = 2;
    /**
     * @var int Force data passed into the constructor as a JSON string. Using this option leads
     * to exceptions if the JSON string is not valid. This option only affects the constructor. Note
     * that the constructor checks a string data as a JSON string by default.
     */
    const AS_JSON = 8;
}
