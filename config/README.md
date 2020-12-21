# Configuration files

## Extensions

Extension configuration in [extension.json](extension.json).

Field                 | Type    | Required | Default | Description
----------------------|---------|----------|---------|------------
build                 | object  | yes      | -       | Build definition
build.deps            | object  | no       | -       | Build package dependencies
build.deps.`{osname}` | array   | yes      | `[]`    | List of packages to be installed prior to extension building
build.flag            | string  | no       | `empty` | Install flag
build.path            | string  | no       | `empty` | Base path to start building the extension
build.type            | string  | yes      | `empty` | Repository type
build.url             | string  | yes      | `empty` | Source repository URL
disabled              | boolean | no       | `false` | Disable extension build/check
pecl                  | boolean | no       | `true`  | Test PECL install
require               | object  | no       | -       | PHP requirements for extension building
require.max           | string  | no       | `empty` | Maximum PHP supported version
require.min           | string  | no       | `empty` | Minimum PHP required version
require.zts           | boolean | no       | `false` | Require ZTS (Thread Safe)
summary               | string  | no       | `empty` | Extension summary

## Operating Systems

Operating System configuration in [operating-systems.json](operating-systems.json).

Field     | Type    | Required | Default | Description
----------|---------|----------|---------|------------
deps      | object  | yes      | -       |
deps.cmd  | string  | yes      | `empty` | Command to be used when a dependency has to be installed
deps.list | array   | no       | `[]`    | List of general dependencies that are used in the build process
disabled  | boolean | no       | `false` | Disable OS from build matrix
pre       | array   | no       | `empty` | List of commands to be executed before building an extension

## PHP Versions

PHP version list in [php-versions.json](php-versions.json).
