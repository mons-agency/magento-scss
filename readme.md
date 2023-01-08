# Magento 2 - SCSS Preprocessor module
This module allows compiling SCSS files in Magento 2 themes, just like LESS files.
It uses `scssphp` library and process standard `@import` instruction as well as `@magento_import`.

## Features
- [x] Themes inheritance support
- [x] Special instruction `@magento_import` processing
- [x] Source maps generation (experimental)
- [ ] Support for NodeJS development tools (tasks runners)

## Installation
1. Install module via composer `composer require mons/module-m2-scss`
2. Register module `php bin/magento setup:upgrade`
3. Compile SCSS theme using `php bin/magento setup:static-content:deploy -f`

## Example theme
* Coming soon...

## Tested with
* Magento 2.4
* PHP 8.1
