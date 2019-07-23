# Json

![Release](https://img.shields.io/github/tag/machitgarha/json.svg?label=Release&color=darkblue&style=popout-square)
![License](https://img.shields.io/github/license/machitgarha/json.svg?label=License&color=darkblue&style=popout-square)

## Why Json?

### Performance

Thinking of performance? Json is here. Many methods have been tested to perform as fast as they could. Sometimes, they are faster than internals. Really? Test it yourself!

### Flexible

- Json is not only for JSON data. So what?
Many types are supported, including JSON strings, arrays, objects and scalars (+ null). Resources are not supported, and won't be supported (because of the standards).
- Use methods regardless of the data type. Merging two objects? Actually.
- Do anything, even if not provided by the class, using a callable. Alternatives? Extend from the class and define your own things. For sure, it will be easy in both cases.
- Oh, what if an index does not exist? Exception. Parsing bad JSON data? Exception, again. Warnings and notices? Very rare cases.

### Many Ways

Like JavaScript dots to get indexes, or native PHP arrays? Choose. Even with JavaScript-like indexes, you can pass the index to a method or you can use `Json::index()`. It is your decision.

## Example

An example from [PHPUnit](https://github.com/sebastianbergmann/phpunit/blob/256901b90d55163005669ec29d5646c357f3d7ef/src/Util/Json.php#L24) source code:

```
function prettify(string $json): string {
    $decodedJson = \json_decode($json, true);

    if (\json_last_error()) {
        throw new Exception(
            'Cannot prettify invalid json'
        );
    }

    return \json_encode($decodedJson, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES);
}
```

Looks good, but it can be really better:

```
// use MAChitgarha\Component\Json

function prettify(string $jsonStr): string {
    return Json::new($jsonStr)->getAsJson(\JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES);
}
```

Advantages:

- Handling different exceptions is easier. Not just getting "Cannot prettify invalid json". Get exception message based on the error happened. Debugging will be easier.
- Less code. Looks prettier. One line. Besides, sometimes, you don't even need to define that method, use Json directly without a function overhead.

## Good! What Do I Need?

Almost nothing. PHP7, the [JSON extension](https://www.php.net/manual/en/book.json.php) that comes with PHP7 by default, and Composer.

## Installing

## Documentation
See [the wiki](https://github.com/MAChitgarha/Json/wiki).

## Installing
You can easily install it with Composer:

```
composer require machitgarha/json
```
