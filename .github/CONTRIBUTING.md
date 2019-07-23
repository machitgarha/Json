
Thank you for your decision. Contributing to this repository is really welcomed. If you want to report something important, contact me via machitgarha@outlook.com.
# Code of Conduct

Before any contributions, you must follow all guidelines in [Code of Conduct](CODE_OF_CONDUCT.md).

# How to Contribute to Code?

For best results, consider the followings:

## First Contribution Notes

If you are contributing for the first time, don't fear. Follow the guidelines [here](https://github.com/firstcontributions/first-contributions). After cloning the repository, follow these steps:

1. __Create a new branch__: Go to directory of the cloned repository. Considering the notes comes later, create a branch with an appropriate name (i.e. mention what you want to change, in short). Checkout the newly-created branch.

    ```
    git checkout -b branch-name
    ```

2. __Grab required dependencies__: First, install [Composer](https://getcomposer.org/) (if you have not installed yet). You should read [its documentation](https://getcomposer.org/doc/00-intro.md) for more details. Then, install the required dependencies using Composer:

    ```
    composer install
    ```

3. __Make changes you want__: Again, considering what comes later, make changes to the code. Commit early, and write good commit messages. You should read [this](https://sethrobertson.github.io/GitBestPractices/) for best git practices.

4. __Other steps__: Continue the other steps following the link in beginning of the section. Push changes and submit them.

## Branching

The branches comes in details:

- `master`: Except `develop` branch, no other branches will be merged into `master` branch. No pull requests must be made on this branch; otherwise, it will be rejected.
- `develop`: All pull requests and merges to `master` branch happens in this branch. So, before making any changes, make sure your branch is created from `develop` branch. 
    
## Versioning

Every tag and release must follow [SemVer](https://semver.org/) rules. When possible, pre-releases should be created. Good tags and releases make the pull request the better chance to be accepted.

## Testing and Analysis

For the best results, Json uses [Travis CI](https://travis-ci.org). Although it runs static analysis, for example, but you should run checks yourself before creating a pull request.

As a result, you should:

- __Run static analysis__

    ```
    ./vendor/bin/phan
    ```

- __Run tests__:

    ```
    ./vendor/bin/phpunit
    ```

- __Fix code styles__:

    ```
    ./vendor/bin/php-cs-fixer fix .
    ```

## Documentation

Keep the code well-documented, please!

## Highly Accepted Contributions

- Adding tests, even without any other changes to the code itself.

## Do not

- Contribute code style fixes.
