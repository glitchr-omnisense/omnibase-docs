# Changelog

## [Unreleased]

## [2.0.1] - 2026-11-14

### Changed

- The classes omnisense/omnibase 5.0 moved to omnibridge/symfony named by their new names:
  `Omnibridge\Symfony\Omnibase\Bundle\{AbstractBaseBundle, AbstractBaseExtension, AbstractBaseConfiguration}`
  in place of `Base\Bundle\*`, former names the core keeps only as aliases until 6.0. No behaviour change.
- BareTest expects omnibridge/symfony's AbstractBaseBundle as the bundle's parent.

## [2.0.0] - 2026-11-13

First stable release.

### Changed

- Installs with the default minimum-stability: stable requirements (`omnisense/omnibase` ^5.1, `omnibridge/symfony` ^1.1), no
  `minimum-stability: dev` left to the sites; the branch 2.0 is the series (Composer reads it as
  2.0.x-dev), so no branch alias.
- CI: glitchr-studio/ci's package workflow (the bundle installed alone, composer validate, PHPUnit,
  PHPStan against its baseline, a consumer install without require-dev) and its release workflow.
