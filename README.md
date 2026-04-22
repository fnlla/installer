# fnlla/installer

Official installer CLI for bootstrapping Fnlla applications.

[![CI](https://github.com/fnlla/installer/actions/workflows/ci.yml/badge.svg)](https://github.com/fnlla/installer/actions/workflows/ci.yml)

## Install

```bash
composer global config repositories.fnlla-installer vcs https://github.com/fnlla/installer
composer global require fnlla/installer:dev-main
```

Ensure Composer global `vendor/bin` is in your `PATH`.
Packagist publication can be added later for direct `composer global require fnlla/installer`.

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
