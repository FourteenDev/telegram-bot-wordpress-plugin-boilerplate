# Changelog
The format is based on [Keep a Changelog](http://keepachangelog.com/) and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]
### Notes
- [:ledger: View file changes][Unreleased]
### Added
### Changed
### Deprecated
### Removed
### Fixed
### Security

## [v3.3.0] - 2026-08-12
### Added
- Added `Logger` class to log errors and debug messages.
- Added an example method on how to sanitize/validate settings fields.
- Added settings updated notice and `settings_errors()` to settings wrapper view.
- Added `resize: both;` CSS rule to textareas in settings.
- Handled possible initialization errors.
- Added PHPDoc to most of the classes.
### Changed
- Improved `NoticeManager` class.
- Changed return type of settings callback in `Base.php` class to `void`.
- Renamed `Post` service class to `Posts`.
- Updated `.gitignore` file to ignore AI related files and folders.
### Fixed
- Added missing method return types.
- Added most of the missing attribute and function parameters type declarations.
- Added missing dependency injections.
- Added a missing `echo` in settings wrapper view file.
- Fixed undefined variable `$allowed_updates` warning in `Telegram.php` extended class.

## [v3.2.0] - 2026-01-28
### Added
- Added textarea HTML element field (#3).
- Added select HTML element field (#3).
### Fixed
- Fixed `getSettingsValue()` method's return value.
- Fixed a small issue in field callback methods.
### Changed
- Updated copyright year.

## [v3.1.0] - 2026-01-25
### Changed
- Updated/Tidied up `Core` class.
### Removed
- Removed the default submenu with similar name.
### Fixed
- Fixed translations not working.
- Renamed some incorrect constants.
- Fixed initial `null` value for container when activating the plugin.
- Fixed typo in `getRestReponse()` name.

## [v3.0.0] - 2025-04-28
### Notes
❗ New structure ❗
### Added
- Added a `Container` class.
### Changed
- Used `Container` in all other classes.
### Removed
- Removed `getInstance()` methods and singleton codes.

## [v2.1.0] - 2025-04-09
### Added
- Added `ShortcodeManager` class and improved shortcodes handling.
- Added a simple `NoticeManager` class.
- Added a comment for `$hookSuffix` usage in the `Asset` class.

## [v2.0.0] - 2025-02-08
### Added
- Added `Base` model class and an example model.
- Added an example controller.
- Added an example service class (commented).
- Added some example integration classes (commented).
- Added `Asset` class.
- Added i18n support.
- Added a new example submenu with custom design.
- Added checkbox settings field.
- Added a sample shortcode.
- Added (commented) filters to edit ACF JSON files' paths.
- Added a (commented) filter to remove the default submenu.
- Added a statement to check for required plugins before executing the core class.
### Changed
- Improved `Model` class.
- Improved `Base` menu class.
- Renamed 'Settings' folder and classes to 'Menus'.
- Add tabs to settings + Made them much more dynamic.
- Added hooks to `validateSettings()` + Improved the method.
- Renamed the general Helper class to `TelegramHelper`.
### Fixed
- Replaced all `FDTBWPB_TEXT_DOMAIN`s with the actual text domain name.
### Security
- Added `ABSPATH` check to all view files.

## [v1.2.1] - 2024-04-01
### Changed
- Updated Model class and other extended classes based on `v0.82.0` of Longman PHP Telegram Bot library. (#1)

## [v1.2.0] - 2024-03-06
### Changed
- Changed project name.
- Re-formatted/refactored everything.
- Renamed `Views` class to `View` + Updated the `View` class + Renamed `templates/` folder to `views/`.
- Renamed `Settings` class to `Setting` + Updated the `Setting` class + Updated settings views.
- Changed method and variable names to camelCase.
- Changed API endpoints to kebab-case.
- Renamed `/app` folder to `src/`.
- Updated `.gitignore`.
### Fixed
- Removed invalid call to `getStartButtons()` method from /cancel command.

## [v1.1.0] - 2023-06-05
### Added
- Added proxy (middleman server) support.
- Added this changelog file.

[Unreleased]: https://GitHub.com/FourteenDev/telegram-bot-wordpress-plugin-boilerplate/compare/v3.3.0...main
[v3.3.0]: https://GitHub.com/FourteenDev/telegram-bot-wordpress-plugin-boilerplate/compare/v3.2.0...v3.3.0
[v3.2.0]: https://GitHub.com/FourteenDev/telegram-bot-wordpress-plugin-boilerplate/compare/v3.1.0...v3.2.0
[v3.1.0]: https://GitHub.com/FourteenDev/telegram-bot-wordpress-plugin-boilerplate/compare/v3.0.0...v3.1.0
[v3.0.0]: https://GitHub.com/FourteenDev/telegram-bot-wordpress-plugin-boilerplate/compare/v2.1.0...v3.0.0
[v2.1.0]: https://GitHub.com/FourteenDev/telegram-bot-wordpress-plugin-boilerplate/compare/v2.0.0...v2.1.0
[v2.0.0]: https://GitHub.com/FourteenDev/telegram-bot-wordpress-plugin-boilerplate/compare/v1.2.1...v2.0.0
[v1.2.1]: https://GitHub.com/FourteenDev/telegram-bot-wordpress-plugin-boilerplate/compare/v1.2.0...v1.2.1
[v1.2.0]: https://GitHub.com/FourteenDev/telegram-bot-wordpress-plugin-boilerplate/compare/v1.1.0...v1.2.0
[v1.1.0]: https://GitHub.com/FourteenDev/telegram-bot-wordpress-plugin-boilerplate/compare/v1.0.0...v1.1.0
