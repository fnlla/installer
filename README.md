# fnlla/installer

Official installer CLI for bootstrapping Fnlla applications.

## Install

```bash
composer global require fnlla/installer
```

Ensure Composer global `vendor/bin` is in your `PATH`.

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
