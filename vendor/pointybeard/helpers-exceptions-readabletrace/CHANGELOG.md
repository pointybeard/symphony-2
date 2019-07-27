# Change Log

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

**View all [Unreleased][] changes here**

## [1.0.2][]
#### Changed
-   Added `pointybeard/helpers-functions-debug` package
-   `ReadableTraceException::getReadableTrace()` now wraps functionality of `Debug\readable_debug_backtrace`

## [1.0.1][]
#### Added
-   Added `$format` argument to `ReadableTraceException::getReadableTrace()` which allows changing how each line of the trace looks.

#### Changed
-   Trace is no longer appended to the exception message. This gives better control to how/when the trace gets displayed.

## 1.0.0
#### Added
-   Initial release

[Unreleased]: https://github.com/pointybeard/helpers-cli-progressbar/compare/1.0.2...integration
[1.0.2]: https://github.com/pointybeard/helpers-cli-progressbar/compare/1.0.1...1.0.2
[1.0.1]: https://github.com/pointybeard/helpers-cli-progressbar/compare/1.0.0...1.0.1
