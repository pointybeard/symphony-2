# Change Log

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

**View all [Unreleased][] changes here**

## [1.1.0][]
#### Changed
-   Requiring PHP 7.2 or greater
-   Updated `ProgressBar::draw()` so the string printed to the screen will span the entire width of the window. This avoids rendering issues when the overall length of the string changes

#### Added
-  Added `pointybeard/helpers-functions-cli` and `pointybeard/helpers-functions-strings` packages

## [1.0.3][]
#### Fixed
-   Fixed Time helper function namespace.
-   Fixed call to `Colour::colourise()` (was colouriseString).
-   Fixed logic in `hasStarted()`

## [1.0.2][]
#### Fixed
-   Fixed package name for `pointybeard/helper-functions-time`

## [1.0.1][]
#### Fixed
-   Fixed PSR-4 autoload namespace

## 1.0.0
#### Added
-   Initial release

[Unreleased]: https://github.com/pointybeard/helpers-cli-progressbar/compare/1.1.0...integration
[1.1.0]: https://github.com/pointybeard/helpers-cli-progressbar/compare/1.0.3...1.1.0
[1.0.3]: https://github.com/pointybeard/helpers-cli-progressbar/compare/1.0.2...1.0.3
[1.0.2]: https://github.com/pointybeard/helpers-cli-progressbar/compare/1.0.1...1.0.2
[1.0.1]: https://github.com/pointybeard/helpers-cli-progressbar/compare/1.0.0...1.0.1
