# Architecture Decisions

## DEC-001
Use native Gutenberg core blocks whenever possible.

## DEC-002
Style precedence is Theme/Core < Global < Post < Block.

## DEC-003
Resolve inheritance property-by-property.

## DEC-004
Ant Design + Tailwind are restricted to dedicated GutenStyle admin screens.

## DEC-005
Tailwind Preflight is disabled and utilities are prefixed.

## DEC-006
Gutenberg interfaces use WordPress-native editor components.

## DEC-007
Frontend styling should require no JavaScript unless interaction requires it.

## DEC-008
Bulk styling assigns post-level profiles/settings rather than rewriting blocks.

## DEC-009
Free owns the style engine; Pro extends Free through public APIs.

## DEC-010
Free and Pro use separate repositories. Free public; Pro private.

## DEC-011
Pin the JavaScript development toolchain to exact compatible versions. Upgrade WordPress tooling as a set, never use `npm audit fix --force`, and assess audit findings separately for shipped runtime code and development-only tooling.

## DEC-012
Store GutenStyle data as versioned structured documents. Global uses a non-autoloaded option, Post/Page/CPT uses registered post meta, and an individual native block uses the `gutenstyle` attribute object.

## DEC-013
A reset removes the explicit value at that scope. It never copies the currently inherited value into storage.

## DEC-014
Presets are declarative property sets, not opaque themes. They are schema-validated and merged property-by-property through the same resolver as explicit overrides.

## DEC-015
An effective property records its scope origin independently from whether it came through a preset or an explicit property. A missing GutenStyle value remains Theme/Core owned.

## DEC-016
Block adapters own block-specific mappings. The generic compiler emits only registered `--gutenstyle-*` declarations, while adapters may reserve native WordPress Style Engine paths for properties WordPress can represent.

## DEC-017
Core PHP tests run against the plugin's minimum PHP 7.4 contract through an exact PHP-WASM development dependency. This keeps engine verification available without requiring a system-wide PHP installation or a running Docker daemon.
