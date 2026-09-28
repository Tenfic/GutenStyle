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
