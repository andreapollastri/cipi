Changelog
===
 
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

(no unreleased versions)

## [2.4.9] - 2020-05-15
### Fixed
- Bug fix to solve alias creation/destroy in `AliasesController.php` e `aliasdel.sh`.

## [2.4.8] - 2020-05-15
### Fixed
- Bug fix to solve right alias creation in `AliasesController.php`.

## [2.4.7] - 2020-05-13
### Fixed
- Bug fix on user permissions in `hostadd.sh` and `hostdel.sh`.

## [2.4.6] - 2020-05-12
### Fixed
- Bug fix on `install.sh`, `hostadd.sh` and `ssl.sh` for http\2 support

## [2.4.5] - 2020-05-12
### Fixed
- Bug fix on `install.sh`, `ApplicationsController.php`, `AliasesController.php`, `hostadd.sh` and `ssl.sh` for http\2 support

## [2.4.4] - 2020-05-11
### Fixed
- Bug fix on `install.sh` for http\2 support

## [2.4.3] - 2020-05-11
### Changed
- Improvements in `haget.conf` for http\2 support

## [2.4.2] - 2020-05-11
### Changed
- Improvements of file2ban's security policies in `install.sh`

## [2.4.1] - 2020-05-11
### Fixed
- Bug fix on `ApplicationsController.php` and `host-del.sh` to fix host destroy
### Changed
- Improvements of file2ban's security policies in `install.sh`

## [2.4.0] - 2020-05-11
### Fixed
- Bug fix on `install.sh` to fix nginx lock and phpmyadmin configuration

## [2.3.3] - 2020-05-11
### Fixed
- Bug fix on `ShellsControllers.php`, `install.sh`, `ApplicationsControllers.php` and `AliasesControllers.php` to fix applications and Aliases creation, nginx default configuration and phpmyadmin configuration

## [2.3.2] - 2020-05-11
### Fixed
- added dynamic remote URL in `hostadd.sh`, `aliasadd.sh`, `ApplicationsControllers.php` and `AliasesControllers.php` to fix migration Cipi compatibility
### Changed
- improvements in `haget.conf`, `install.sh` and `phpfpm.conf` to optimize PHP-FPM and nginx performance

## [2.3.1] - 2020-05-09
### Fixed
- Bugfix on `Server.php` to fix Cipi data migration import (now server status is included in the migration)

## [2.3.0] - 2020-05-09
### Added
- Added `LICENSE` and `CHANGELOG.md` files
### Fixed
- Bugfix on `SettingsController.php` to fix Cipi data migration export
### Changed
- improvements in `18.sh` and `20.sh`
- Added Github icons in `Readme.md`
