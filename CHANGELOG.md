# Json Package Changelog

**Note:** BC means Backward Compatible, and BC Break means Backward Incompatible (i.e. Backward Compatibility Break).

## 2.0.0

**Important Note:** You see many backward incompatibility changes. The reason is, the purpose of Json package was changed since this version, and the code was rewritten almost completely. This means, you can fork version 1 of Json and move on, but totally _it is not recommended_, as there were a lot of performance issues, and some bugs also.

### 2.0.0-alpha.1

#### Added

##### General

-   Added providers and interactor interfaces. Providers are classes providing functionality for Json methods. Interactors are the layer between, which means, classes interacting between providers and Json class.

-   Added option containers, classes containing options for various functionalities. Every option container _must_ extend from `OptionContainer` base class.

##### Interfaces

-   New interactor interfaces:
    -   `BaseInteractorInterface`
        -   `LinterInteractorInterface`
        -   `EncoderInteractorInterface`
        -   `DecoderInteractorInterface`

-   New option containers:
    -   `EncodingOption`: For `Json::encode()`:
        -   `PRETTY_PRINT`: Well-formatted human-readable output.
    -   `DecodingOption`: For `Json::decode()`.
    -   `InitOption`: For `Json::__construct()`:
        -   `SCALAR_DATA`: The default behaviour of Json class should be, if the data passed to constructor is a string, it should be treated as JSON string instead of a basic string. This option determines that the data is not a JSON string.

-   `Data`: New class for handling data between Json and interactors. This has the advantage of not copying the same data over and over. There is only one instance of data saved in Json class, and if needed, it is being passed across the interactors.

##### Methods

-   `Json::lint()`
-   `Json::encode()`
-   `Json::decode()`

#### Changed (BC Break)

##### General

-   Changed the whole namespace hierarchy. Everything is now under `MAChitgarha\Json` namespace, and all namespaces are now in plural form (e.g., `Component` converted to `Components`).

##### Methods

-   `Json::__construct()`:
    -   Changed options type from integer to array. The new syntax for passing options is now a mapper of names of option container classes (i.e. strings) to the set of options related to that container (as integers).
    -   Move options parameter from second parameter to third.
    -   The second parameter is now an instance providers container.

-   Add option container names as first parameter and move option parameter to second for all these methods:
    -   `Json::setOptions()`
    -   `Json::addOption()`
    -   `Json::removeOption()`
    -   `Json::isOptionSet()`

##### Properties

-   `Json::$options`: Its visibility changed from protected to private. There is no need for the child class to access it, as it should use methods like `Json::setOptions()`. Also, due to changes to whole option system, changed its type from integer to array.

#### Removed (BC Break)

##### Methods

-   `Json::encodeToJson()` (static). Use `Json::encode()` (non-static) instead.
-   `Json::decodeJson()` (static). Use `Json::decode()` (non-static) instead.

##### Constants

-   `JsonOpt::AS_JSON`: Due to addition of `InitOption::SCALAR_DATA` option.

**Note:** This changelog was added from version 2.0.0. For older changelog, refer to the commit messages or the release notes.
