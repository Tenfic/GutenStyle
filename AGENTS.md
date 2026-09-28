# GutenStyle Agent Instructions

## Product
GutenStyle is a visual style and design system for native WordPress Gutenberg content.

## Core principle
Enhance native WordPress blocks instead of replacing them whenever possible.

## Style precedence
Theme/Core
< GutenStyle Global
< GutenStyle Post/Page/CPT
< GutenStyle Individual Block
< Explicit user-set Gutenberg property when applicable

Resolve styles property-by-property rather than replacing an entire inherited style object.

## Free/Pro architecture
- GutenStyle Free owns the engine, registries, resolver, public extension API, and core integrations.
- GutenStyle Pro is a separate private add-on and depends on Free.
- Never place premium implementation code in the public Free plugin and hide it behind a license check.
- Free must remain independently buildable from its public source repository.
- Pro must extend Free through public APIs/hooks/contracts.

## Admin UI
- React + TypeScript.
- Ant Design for interactive UI components.
- Tailwind CSS for layout/utilities.
- Tailwind Preflight must remain disabled.
- Prefix Tailwind utilities with `gs:`.
- Prefix Ant Design components with `gutenstyle`.
- Admin assets must load only on GutenStyle pages.

## Gutenberg UI
- Use native WordPress packages: @wordpress/components, @wordpress/block-editor, @wordpress/data, @wordpress/api-fetch.
- Do not use Ant Design inside Gutenberg unless explicitly approved.

## Frontend
- Prefer native Gutenberg markup.
- Prefer CSS and CSS custom properties.
- Avoid frontend JavaScript unless behavior requires it.
- Never introduce content lock-in unnecessarily.

## Security
- Validate, sanitize, and escape.
- REST routes require permission_callback.
- Nonces are not authorization; always check capabilities.
- Do not accept unsafe arbitrary CSS from ordinary users.
- Do not expose secrets/license credentials.

## WordPress.org
- Free source must remain publicly buildable.
- Use GPL-compatible dependencies.
- Bundle non-service assets locally.
- Never load ordinary JS/CSS libraries from a CDN at runtime.

## Dependency maintenance
- Never run `npm audit fix --force`.
- Upgrade WordPress development tooling as a compatible set, including `@wordpress/scripts` and `@wordpress/env`.
- Review audit findings by separating development tooling and transitive tooling dependencies from runtime code shipped with GutenStyle.
