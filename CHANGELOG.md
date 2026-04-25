# Changelog

## 1.1.0 - 2026-04-25

### Changed

- Relaxed the Symfony UX component constraints to allow both `2.x` and `3.x`.
- Kept the bundle compatible with the app's Symfony UX 3 upgrade path without changing runtime behavior.

## 1.0.0 - 2026-04-25

### Added

- Added `CHANGELOG.md` for release tracking.
- Added test coverage for empty-path and symlink-escape validation cases.

### Changed

- Modernized the bundle for EasyAdmin 5 compatibility.
- Refreshed the list and detail templates to fit the current EasyAdmin and Bootstrap UI.
- Improved the live component filters and log line presentation.
- Updated package metadata and README documentation for current installation and usage.

### Fixed

- Fixed custom content rendering after the EasyAdmin 5 template block changes.
- Hardened log file path validation using resolved paths.
- Made log date parsing resilient to malformed dates.
- Improved delete action error handling.
