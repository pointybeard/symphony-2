# Change Log

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## [1.0.4][]
#### Added
-   Added `ClassRegistryInterface`

#### Changed
-   Renamed `FactoryRegistry` to `ClassRegistry`
-   Updated `Factory\create()` to use `ClassRegistry`

## [1.0.3][]
#### Changed
-   Updated `Factory\create()` to use `Strings\random_unique_classname()`

## [1.0.2][]
#### Changed
-   Made all methods in `AbstractFactory` non-static with exception of `build()`, which has not changed
-   `getExpectedClassType()` and `getTemplateNamespace()` are no longer static
-   Added `FactoryRegistry` class to keep track of runtime factories.
-   Added `Factory\create()` function which enables the creation of factory classes at runtime.

## [1.0.1][]
#### Changed
-   Added `AbstractFactory::build()` method which accepts the name of the class to instanciate and arguments (packed using the splat operator) to pass to the constructor (via `AbstractFactory::instanciate()`)
-   Removed `hasSimpleFactoryBuildMethodTrait`

## 1.0.0
#### Added
-   Initial release

[1.0.4]: https://github.com/pointybeard/helpers-foundation-factory/compare/1.0.3...1.0.4
[1.0.3]: https://github.com/pointybeard/helpers-foundation-factory/compare/1.0.2...1.0.3
[1.0.2]: https://github.com/pointybeard/helpers-foundation-factory/compare/1.0.1...1.0.2
[1.0.1]: https://github.com/pointybeard/helpers-foundation-factory/compare/1.0.0...1.0.1
