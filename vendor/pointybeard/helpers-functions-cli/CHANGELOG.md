# Change Log

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## [1.1.8][]
#### Changed
-   Updated `manpage()` to work with `pointybeard/helpers-cli-input` 1.2
-   Using v1.2.x of `pointybeard/helpers-cli-input`
-   Updated version constraints in `composer.json`

## [1.1.7][]
#### Added
-   Added `pointybeard/helpers-functions-debug` package

#### Changed
-   Using `readable_debug_backtrace()` (provided by `pointybeard/helpers-functions-debug`) in `display_error_and_exit()` to produce a trace if one is provided

## [1.1.6][]
#### Added
-   Added `display_error_and_exit` function

## [1.1.5][]
#### Changed
-   Updated to work with `pointybeard/helpers-cli-input` v1.1.x

## [1.1.4][]
#### Changed
-   Refactoring of `manpage()` to hide 'Options' and/or 'Arguments' if there are none to show

## [1.1.3][]
#### Changed
-   Updated `manpage()` to include `foregroundColour`, `headingColour`, and `additional` arguments. Removed `example` argument in favour of including it inside `additional`
-   Added `pointybeard/helpers-cli-colour` composer package

## [1.1.2][]
#### Changed
-   Using latest version of `pointybeard/helpers-functions-strings`

## [1.1.1][]
#### Added
-   Added `get_window_size()` function

## [1.1.0][]
#### Added
-   Added `manpage` and `usage` functions

#### Changed
-   Requiring PHP 7.2 or greater
-   Including `pointybeard/helpers-cli-input`, `pointybeard/helpers-functions-strings`, and `pointybeard/helpers-functions-flags` composer packages

## 1.0.0
#### Added
-   Initial release

[1.1.8]: https://github.com/pointybeard/helpers-functions-cli/compare/1.1.7...1.1.8
[1.1.7]: https://github.com/pointybeard/helpers-functions-cli/compare/1.1.6...1.1.7
[1.1.6]: https://github.com/pointybeard/helpers-functions-cli/compare/1.1.5...1.1.6
[1.1.5]: https://github.com/pointybeard/helpers-functions-cli/compare/1.1.4...1.1.5
[1.1.4]: https://github.com/pointybeard/helpers-functions-cli/compare/1.1.3...1.1.4
[1.1.3]: https://github.com/pointybeard/helpers-functions-cli/compare/1.1.2...1.1.3
[1.1.2]: https://github.com/pointybeard/helpers-functions-cli/compare/1.1.1...1.1.2
[1.1.1]: https://github.com/pointybeard/helpers-functions-cli/compare/1.1.0...1.1.1
[1.1.0]: https://github.com/pointybeard/helpers-functions-cli/compare/1.0.0...1.1.0
