Changelog
===
 
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

(no unreleased versions)

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
