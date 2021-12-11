Changelog
===
 
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

(no unreleased versions)

## [3.1.8] - 2021-12-11

### Added
- Configurable users and db/path naming prefix
- Selective Dashboard login check

### Fixed
- JWT Key Logic
- PHP 8.0 by default (not 8.1) because WP is not ready to it
- Main welcome page Fix
- Client welcome page Fix
- New Sites Welcome Page
- New Login Page

### Removed
- Mobile App link into sidebar

## [3.1.7] - 2021-12-10

### Added
- Google Bot noindex, nofollow (for Panel)
### Fixed
- Client Server Patch (build 202112101)
- System Update Error Fix
- Site Domains and Paths in lowercase
- SSL Nginx Restart Fix
- PHP Version fix in site PDFs
- Datatable Render Issue
- Minor Template Fix
### Removed
- Documentation link

## [3.1.6] - 2021-12-10
### Fixed
- Mobile App link in menù
- Minor fix in views

## [3.1.5] - 2021-12-10
## Added
- Cipi App link in menù
### Fixed
- PHP 7.3 legacy support fix

## [3.1.4] - 2021-12-10
### Fixed
- Login BG fix

## [3.1.3] - 2021-12-10
### Fixed
- Views fix

## [3.1.2] - 2021-12-10
### Fixed
- Domains conflict fix

## [3.1.1] - 2021-12-09
### Fixed
- Let's Encrypt Issue on Nginx Fix
- Domain Edit Issue Fix

## [3.1.0] - 2021-12-09
### Added
- PHP 8.1 support (default version)
- Optional installation Arg (GIT branch)

### Fixed
- Client Server Patch (build 202112091)
- Domain Aliases Fix
- Node upgrade to v16 (npm to v8)
- Vendor Upgrade
- Nginx Restart Issue Fix
- Certbot vendor update
- Minor Fixed

### Removed
- PHP 7.3 from new installations

## [3.0.10] - 2021-04-28
### Fixed
- Console Kernel Error Fix

## [3.0.9] - 2021-04-28
### Fixed
- Basepath Patch Error
- Supported Cipi Installation on Contabo VPS

## [3.0.8] - 2021-04-15
### Fixed
- Fixed Active Installation Count script
- Auto Update Schedule Fix

## [3.0.7] - 2021-04-15
### Fixed
- Fixed Supervisor Script issue
### Added
- Added Active Installation count

## [3.0.6] - 2021-04-15
### Fixed
- Fixed SSL generation issues
- Fixed new alias creation issues
### Changed
- Typo fix into site section
- Removed unused namespaces and files

## [3.0.5] - 2021-04-14
### Changed
- Fixed sidebar active class bug
- Added documentation in sidebar
- Auto update script improvements
- Readme update

## [3.0.4] - 2021-04-13
### Changed
- CS fix and improvements on PHP codebase
- Added branch var into go.sh file

## [3.0.3] - 2021-04-10
### Fixed
- Auto update script issue

## [3.0.2] - 2021-04-10
### Fixed
- Basepath Patch now manage correctly "empty" values
### Updated
- Update of Server Client and Panel versions

## [3.0.1] - 2021-04-09
### Fixed
- Basepath Patch API issue (it didn't work)
- Typo fix in codebase
### Changed
- Updated `readme.md` version 3 docs and screenshots information
### Upgrade
- Updated PHPsec vendor


## [3.0] - 2021-03-25
### New Features
- Move to Laravel 8
- PHP 8 Support
- Now you can manage the same server that runs Cipi
- Auto version update (so you don't need to reinstall it)
- API REST (with Swagger OA http://YOUR-IP/api/docs)
- Cronjob editor
- New Queue system to deploy servers
- node 15, mysql 8, ffmpeg, composer 2 and other extensions
- PHP FPM / PHP CLI selector
- Supervisor manager
- Domain / basepath manager
- Github repository manager
- CPU / RAM realtime charts
- JWT authentication
- Improvements on UI/UX
### Fixed
- AWS and other provider installation issues

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
