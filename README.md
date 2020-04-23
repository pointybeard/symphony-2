# Symphony CMS (Extended)

Symphony is a `PHP` & `MySQL` based CMS that utilises `XML` and `XSLT` as its core technologies. This repository is based on [Symphony CMS 2.7.10](https://github.com/symphonycms/symphonycms/tree/2.7.x) and is considered stable.

This specific fork of the official Symphony CMS 2.7.x release includes various quality of life improvements and changes. Most notably:

- Support for [JSON formatted config file](#2-json-formatted-config)
- Added [pre-boot scripts](#1-pre-boot-scripts) feature for custom boot behaviour
- [Removal of the all built-in fields](#3-removal-of-all-built-in-fields)
- [Support for additional XSLT processors](#4-support-for-additional-xslt-processors)

As well as other minor enhancements to the code base making future expansion of the core much easier.

This build will continue to be maintained against the official 2.7.10 LTS release.

-   [Requirements](#requirements)
-   [Installation](#installation)
    -   [master](#master)
    -   [essentials](#essentials)
-   [Key Features](#key-features)
    -   [Pre-boot scripts](#pre-boot-scripts)
    -   [JSON formatted config](#json-formatted-config)
    -   [Removal of built-in fields](#removal-of-built-in-fields)
    -   [Support for additional XSLT processors](#support-for-additional-xslt-processors)
-   [Support](#support)
-   [Contributing](#contributing)
-   [Author](#author)
-   [License](#license)

## Requirements

This build of Symphony CMS requirements differ slightly to the official release. Most notably, it will only run on PHP 7.3 or newer. It is suggested that your server is running at least PHP 7.3 to ensure full compatiblity with newer extensions.

It is also assumed that the server has Composer pre-installed. Unlike official builds that come with the composer depencies baked in, you will need to run composer in order to load all the reqired libraries. See the [Composer "Getting Started" documentation](https://getcomposer.org/doc/00-intro.md) for instructions.

## Installation

There are 2 main branches to the Symphony CMS (Extended) repository `master` and `essentials`. Each follow a slightly different installation pathway.

### master

This is the main branch is a drop-in replacement and can be installed/updated like any other official 2.x release (including migrating from older versions). To clone from git, use the following

```bash
$ git clone --depth 1 https://github.com/pointybeard/symphonycms.git symphonycms
$ composer update -vv --no-dev --profile -d ./symphonycms
```

Then, follow the instructions contained in the [official 2.7.x release README doc](https://github.com/symphonycms/symphonycms/blob/2.7.x/README.markdown).

### essentials

This branch removes files such as the install and update scripts, index.php, and a few other things, in order to make it installable as a composer dependency in other projects. Notable examples being [Symphony CMS: Section Builder](https://github.com/pointybeard/symphony-section-builder) and [Orchestra](https://github.com/pointybeard/orchestra).

As such, this cannot be installed in the standard way. Add the following to your project's `composer.json` file

```json
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/pointybeard/symphonycms.git"
    }
],
"require": {
    "symphonycms/symphonycms": "dev-essentials"
}
```

## Key Features

This build of Symphony CMS makes some fairly substantial changes which would be unlikely to be accepted back into the official repository.

The key reason this particular build was created was to support the development of [Orchestra](https://github.com/pointybeard/orchestra) which is a meta package for scaffolding and rapidly deploying Symphony CMS builds. Orchestra drastically changes the folder structure of Symphony and provides additional features that wouldn't have been possible otherwise.

This build of Symphony also makes extensions like [Saxon/C](https://github.com/pointybeard/saxon) possible, giving long overdue support for XSLT 3.0.

Here are the most notable changes provided by this build of Symphony CMS:

### Pre-boot scripts

Pre-boot scripts are run prior to the core Symphony engine being instanciated.

Symphony will looks for the `symphony_preboot_config` path environment variable, which is a JSON file describing the scripts to include. Currently it only supports including additional files but in future it might include other tasks.

To use the pre-boot behaviour, follow these steps:

1. Set `symphony_enable_preboot` to `1` either via apache envvars or `.htaccess`
2. Set the path to the pre-boot JSON file with the `symphony_preboot_config` environment variable. E.g.

```bash
SetEnv symphony_enable_preboot 1
SetEnv symphony_preboot_config "/some/path/to/preboot.json"
```

3. Create the file specified by `symphony_preboot_config` and list files to include. Here is an example of a pre-boot config:

```json
{
    "includes": [
        "manifest/preboot/01_test.php",
        "/var/www/html/symphony/manifest/preboot/02_test.php"
    ]
}
```

Note, when pre-boot scripts are run, the Symphony core has not been initialised, i.e. there is no database connection and the main autoloader has not been included.

### JSON formatted config

It is now expected that config will be a JSON file. Using JSON instead of an autogenerated PHP file makes loading, parsing, and saving the config much easier. Not to mention more readable.

### Removal of built-in fields

In effort to remove clutter and give developers the option of streamlining their builds even further, all of the built-in fields (Date, Input, Textarea, Select, Taglist, Author, Checkbox, and Upload) have been removed.

To add these fields back in, download and install the [Classic Fields Extension](https://github.com/pointybeard/classicfields). This extension lets you selectively install/uninstall any or all of these core fields as required.

### Support for additional XSLT processors

This feature allows extensions to provide additional XSLT processor libaries which are then registered with Symphony and selectable in System Preferences. For example, [Saxon/C](https://github.com/pointybeard/saxon) adds support for XSLT 3.0.

## Support

If you believe you have found a bug, please report it using the [GitHub issue tracker][ext-issues],
or better yet, fork the library and submit a pull request.

## Contributing

If you would like to contribute to the official Symphony CMS project, [please see the documentation here](https://github.com/symphonycms/symphonycms/wiki/Contributing-to-Symphony).

To contribute to this specific fork, please check out the [Contributing documentation](https://github.com/pointybeard/symphonycms/blob/master/CONTRIBUTING.md) for guidelines about how to get involved.

## Author & Acknowledgements

-   Alannah Kearney - hi@alannahkearney.com - http://twitter.com/pointybeard
-   Symphony CMS Community - https://github.com/symphonycms/symphonycms/graphs/contributors
-   See also the list of [contributors][ext-contributor] who participated in this specific fork

## License

"Symphony CMS (Extended)" is released under the [MIT License][ext-mit]. See [LICENCE.md][doc-LICENCE] for full copyright and license information.

[doc-CONTRIBUTING]: https://github.com/pointybeard/orchestra/blob/master/CONTRIBUTING.md
[doc-LICENCE]: https://github.com/pointybeard/orchestra/blob/master/CONTRIBUTING.md
[ext-MIT]: http://www.opensource.org/licenses/MIT
