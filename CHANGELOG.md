# Json Package Changelog

**Note:** BC means Backward Compatible, and BC Break means Backward Incompatible (i.e. Backward Compatibility Break).

## 2.0.0

**Important Note:** You see many backward incompatibility changes. The reason is, the purpose of Json package was changed since this version, and the code was rewritten almost completely. This means, you can fork version 1 of Json and move on, but totally _it is not recommended_, as there were a lot of performance issues, and some bugs also.

### 2.0.0-alpha.1

#### Added

##### General

-   Added interaction layers as interfaces between feature providers and Json class:
    -   `BaseInteractorInterface`
        -   `LinterInteractorInterface`
        -   `EncoderInteractorInterface`
        -   `DecoderInteractorInterface`

-   Added new option containers (based on new `OptionContainer` base class):
    -   `EncodingOption`: For `Json::encode()`, with following new options:
        -   `PRETTY_PRINT`
    -   `DecodingOption`: For `Json::decode()`, with no options for now.

##### Methods

-   `Json::lint()`
-   `Json::encode()`
-   `Json::decode()`

#### Changed (BC Break)

##### Namespaces

-   Changed the whole namespace hierarchy. Everything is now under `MAChitgarha\Json` namespace, and all namespaces are now in plural form (e.g., `Component` converted to `Components`).

##### Methods

-   `Json::__construct()`:
    -   Changed options type from integer to array. The new syntax for passing options is now a mapper of names of option container classes (i.e. strings) to the set of options related to that container (as integers).

-   Add option container names as first parameter and move option parameter to second for all these methods:
    -   `Json::setOptions()`
    -   `Json::addOption()`
    -   `Json::removeOption()`
    -   `Json::isOptionSet()`

##### Properties

-   `Json::$options`: Its visibility changed from protected to private. There is no need for the child class to access it, as it should use methods like `Json::setOptions()`. Also, due to changes to whole option system, changed its type from integer to array.

#### Removed (BC Break)

##### Methods

-   `Json::encodeToJson()` (static) (Use `Json::encode()` (non-static) instead)
-   `Json::decodeJson()` (static) (Use `Json::decode()` (non-static) instead)

**Note:** This changelog was added from version 2.0.0. For older changelog, refer to the commit messages or the release notes.
