# Change Log

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## [1.2.1][]
#### Changed
-   Updated `InputHandlerFactory` and `InputTypeFactory` to work with changes in `pointybeard/helpers-foundation-factory` 1.0.2

## [1.2.0][]
#### Added
-   Added `InputTypeFilterIterator` class
-   Added `UnrecognisedInputException` exception

#### Changed
-   `InputCollection` now implements `Iterator` and `Countable` (implementing required methods)
-   Removed use of `$type` in `InputCollection`
-   Added `InputCollection::getItemsExcludeByType()`
-   `InputCollection::getItemsByType()` and `InputCollection::getItems()` now returns an `Iterator`
-   Renamed `InputCollection::append()` to `add()` and added `$position` flag
-   Added `POSITION_APPEND` and `POSITION_PREPEND` flags to `InputCollection`
-   Made `getCollection()`, `getInput()`, `find()`, and `validate()` in `AbstractInputHandler` final
-   Removed all categorisation of items by type in `AbstractInputHandler::$input`
-   Abstracted most of `AbstractInputHandler::validate()` into it's own protected method called `validateInput()`
-   Removed `$skipValidation` argument from `AbstractInputHandler::bind()` and relaced with `$flags`
-   Added `FLAG_BIND_SKIP_VALIDATION`, `FLAG_VALIDATION_SKIP_REQUIRED`, `FLAG_VALIDATION_SKIP_CUSTOM`, and `FLAG_VALIDATION_SKIP_UNRECOGNISED` flags to `AbstractInputHandler`
-   Added check in `AbstractInputHandler::validate()` to look for unrecognised options and arguments
-   Removed `InputHandlerFactory::FLAG_SKIP_VALIDATION` from `InputHandlerFactory`
-   Passing flags in call from `InputHandlerFactory::build()` to `AbstractInputHandler::bind()`
-   Updated `InputHandlerInterface::bind()` and `validate()` methods to support flags

## [1.1.4][]
#### Fixed
-   Fixed misnamed variable in `InputCollection::merge()`

## [1.1.3][]
#### Fixed
-   Fixed logic bug that prevented `$index` and `$type` from being set in `InputCollection::append()`. This means replaceing items in an `InputCollection` now works as expected

## [1.1.2][]
#### Added
-   Added `InputValidationFailedException` exception
-   Added `InputTypeInterface::getDisplayName()` method to standardise how the name of an `InputTypeInterface` class wants to display it's name

#### Changed
-   Updated validation logic for inputs that have a validator, no default, and are not set
-   Throwing `InputValidationFailedException` exception when validation fails
-   Updated `RequiredInputMissingException` and `RequiredInputMissingValueException` exceptions to use `InputTypeInterface::getDisplayName()` when producing their message
-   Removed unused `RequiredArgumentMissingException` exception

## [1.1.1][]
#### Changed
-   `AbstractInputHandler::find()` returns NULL if it cannot find any input with the supplied name. It is easier to test for NULL than it is to catch an exception

## [1.1.0][]
#### Added
-   Expanded input types to include `Flag`, `IncrementingFlag`, and `LongOption`
-   Added `InputTypeFactory` to help with loading input type classes

#### Changed
-   Updated to work with more than just `Argument` and `Option` input types. Makes use of `InputTypeFactory` to allow addition of new types as needed

## [1.0.2][]
#### Changed
-   Updated example to reflect changes to `manpage()` function in `pointybeard/helpers-functions-cli` package
-   Refactoring and improvemnts to `Argument::__toString()` and `Option::__toString()`

## [1.0.2][]
#### Fixed
-   Fixed `InputCollection::getArgumentsByIndex()` so it returns NULL if the index does not exist instead of throwing an E_NOTICE message

## [1.0.1][]
#### Changed
-   Updated example to use `Cli\manpage()` provided by the `pointybeard/helpers-functions-cli` package

## 1.0.0
#### Added
-   Initial release

[1.2.1]: https://github.com/pointybeard/helpers-functions-cli/compare/1.2.0...1.2.1
[1.2.0]: https://github.com/pointybeard/helpers-functions-cli/compare/1.1.4...1.2.0
[1.1.4]: https://github.com/pointybeard/helpers-functions-cli/compare/1.1.3...1.1.4
[1.1.3]: https://github.com/pointybeard/helpers-functions-cli/compare/1.1.2...1.1.3
[1.1.2]: https://github.com/pointybeard/helpers-functions-cli/compare/1.1.1...1.1.2
[1.1.1]: https://github.com/pointybeard/helpers-functions-cli/compare/1.1.0...1.1.1
[1.1.0]: https://github.com/pointybeard/helpers-functions-cli/compare/1.0.3...1.1.0
[1.0.3]: https://github.com/pointybeard/helpers-functions-cli/compare/1.0.2...1.0.3
[1.0.2]: https://github.com/pointybeard/helpers-functions-cli/compare/1.0.1...1.0.2
[1.0.1]: https://github.com/pointybeard/helpers-functions-cli/compare/1.0.0...1.0.1
