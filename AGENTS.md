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

## Local development environments
- Use Node `>=24.18.0 <25` and npm `>=11.16.0 <12`; do not change runtime versions to work around Playground startup.
- Docker-free development: `npm ci`, `npm run build`, then `npm run env:playground:start` from the `gutenstyle` repository root. Run `npm start` in a second terminal for asset watching.
- Playground is the pinned official `@wp-playground/cli@3.1.56`, using the explicit WordPress `7.1.2` release ZIP and PHP `8.3`. Do not replace the ZIP with `latest` or a version-discovery lookup.
- Site: `http://localhost:8888`; GutenStyle admin: `/wp-admin/admin.php?page=gutenstyle`. The local-only login is `gutenstyle` / `GutenStyleLocal!8888`.
- `playground/blueprint.json` creates the local administrator only if absent and activates the repository mounted at `/wordpress/wp-content/plugins/gutenstyle`. Keep the checkout folder named `gutenstyle`.
- Stop Playground with Ctrl+C in its terminal. There is no official standalone CLI stop subcommand.
- Site data persists under `%USERPROFILE%\.wordpress-playground\sites\<path-hash>` (the CLI prints the exact path). Restart from the same checkout to preserve data.
- `npm run env:playground:reset` deletes that checkout's local Playground site and starts fresh. Use only when explicitly requested; preserve user-created local data otherwise.
- Keep `.wp-env.json` valid and Docker-oriented, without an unsupported `runtime` key. Keep `@wordpress/env` and `env:start`/`env:stop` for Docker/MySQL and release QA. Do not repeatedly retry wp-env's Playground runtime when its version-cache lookup is failing.
- Do not run both environments on port 8888. Do not expose the CLI development server publicly or reuse real credentials/data. Do not blindly approve package install scripts or disable TLS/security checks to make startup work.
