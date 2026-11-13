# Changelog

## [Unreleased]

## [2.0.0] - 2026-11-13

First stable release.

### Changed

- Installs with the default minimum-stability: stable requirements (`omnisense/omnibase` ^5.1, `omnibridge/symfony` ^1.1), no
  `minimum-stability: dev` left to the sites; the branch 2.0 is the series (Composer reads it as
  2.0.x-dev), so no branch alias.
- CI: glitchr-studio/ci's package workflow (the bundle installed alone, composer validate, PHPUnit,
  PHPStan against its baseline, a consumer install without require-dev) and its release workflow.
