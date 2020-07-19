# Json Package Changelog

**Note:** BC means Backward Compatible, and BC Break means Backward Incompatible (i.e. Backward Compatibility Break).

## 2.0.0

**Important Note:** You see many backward incompatibility changes. The reason is, the purpose of Json package was changed since this version, and the code was rewritten almost completely. This means, you can fork version 1 of Json and move on, but totally _it is not recommended_, as there were a lot of performance issues, and some bugs also.

### 2.0.0-alpha.1

#### Added

##### General

-   Add interaction layers as interfaces between feature providers and Json class:
    -   `BaseInteractorInterface`
        -   `LinterInteractorInterface`
        -   `EncoderInteractorInterface`
        -   `DecoderInteractorInterface`

-   Add new option providers:
    -   `EncodingOption`: For `Json::encode()`, with following new options:
        -   `PRETTY_PRINT`
    -   `DecodingOption`: For `Json::decode()`, with no options for now.

##### Methods

-   `Json::lint()`
-   `Json::encode()`
-   `Json::decode()`

#### Changed (BC Break)

##### Namespaces

-   Change the whole namespace hierarchy. Everything is now under `MAChitgarha\Json` namespace, and all namespaces are now in plural form (e.g., `Component` converted to `Components`).

#### Removed (BC Break)

-   `Json::encodeToJson()` (static) (Use `Json::encode()` (non-static) instead)
-   `Json::decodeJson()` (static) (Use `Json::decode()` (non-static) instead)

**Note:** This changelog was added from version 2.0.0. For older changelog, refer to the commit messages or the release notes.
