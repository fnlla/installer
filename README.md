# fnlla/installer

Official installer CLI for bootstrapping Fnlla applications.

[![CI](https://github.com/fnlla/installer/actions/workflows/ci.yml/badge.svg)](https://github.com/fnlla/installer/actions/workflows/ci.yml)

## Install

```bash
composer global require fnlla/installer
```

Ensure Composer global `vendor/bin` is in your `PATH`.
For bleeding-edge development you can still install `dev-main` from VCS.

## Usage

```bash
fnlla new my-app
```

This runs:

```bash
composer create-project fnlla/fnlla my-app --prefer-dist --no-interaction
```

## Examples

```bash
fnlla new my-app --stability=stable
fnlla new my-app --prefer-source
fnlla new my-app --force
```

## Notes

- `fnlla/fnlla` remains the canonical starter template.
- Installer focuses on DX and consistent bootstrap defaults.
- Packagist auto-update is driven by GitHub webhook (`https://packagist.org/api/github`) with workflow fallback in `.github/workflows/packagist-fallback-update.yml`.

## Release

- Changelog: `CHANGELOG.md`
- Manual release workflow: `.github/workflows/release.yml`
