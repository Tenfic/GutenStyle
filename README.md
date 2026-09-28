# GutenStyle

GutenStyle is a visual styling system for native Gutenberg blocks.

## Development Environment

Requirements:

- Node.js 24.18.0 or newer in the Node 24 release line
- npm 11.16.0 or newer in the npm 11 release line
- Git
- Docker Desktop only for the separate wp-env/release-QA workflow

Install the pinned dependencies:

```bash
npm ci
```

Build the assets once, then start local WordPress without Docker:

```bash
npm run build
npm run env:playground:start
```

In another terminal, keep the asset watcher running:

```bash
npm start
```

The Playground fallback uses the official `@wp-playground/cli@3.1.56`, WordPress
`7.1.2`, and PHP `8.3`. It downloads the explicit official
`https://wordpress.org/wordpress-7.1.2.zip` archive, bypassing the CLI's version
discovery API. PHP's patch/build is supplied by the pinned Playground runtime.
First startup still requires internet access for runtime/WordPress downloads.

Local URLs:

- http://localhost:8888
- Admin: http://localhost:8888/wp-admin/
- GutenStyle: http://localhost:8888/wp-admin/admin.php?page=gutenstyle

Playground development credentials:

- username: `gutenstyle`
- password: `GutenStyleLocal!8888`

These are disposable local-only credentials. The startup blueprint creates that
administrator only if absent and activates GutenStyle on each start. It does not
reset an existing user's password or overwrite posts/settings. Automatic browser
login is disabled. The CLI also creates its own default `admin` account. Do not
expose this development server to the internet or use production data/credentials;
the CLI's server can listen on network interfaces beyond localhost.

Run commands from this repository (`D:\gutenstyle` on this machine). The repository
is live-mounted at `/wordpress/wp-content/plugins/gutenstyle`, so changes to PHP
and watcher-generated assets are immediately available. No plugin ZIP or copying
is required. Keep this checkout folder named `gutenstyle` for the CLI's automatic
mount and blueprint activation path to agree.

Stop Playground with **Ctrl+C in its terminal**. The official CLI has no separate
stop subcommand, so there is no misleading `env:playground:stop` script.

The `start` command persists WordPress files, SQLite database, uploads, posts,
users, and settings under `%USERPROFILE%\.wordpress-playground\sites\<path-hash>`.
It prints the exact directory on startup. Restart from the same repository path
to reuse that site. Moving the checkout changes the site identity. The mounted
plugin source remains in the repository; it is not copied into the site backup.

To deliberately discard this checkout's Playground site and start fresh, first
stop the running server, then run:

```bash
npm run env:playground:reset
```

Reset deletes only the CLI-managed site for the current checkout, including its
database and uploads. It does not delete the repository or wp-env data. Back up
the printed site directory first if the local content matters. Do not run reset
as a routine startup or network troubleshooting step.

Port 8888 is fixed; stop another server using it before starting Playground. The
Docker and Playground environments cannot run on that port simultaneously.

### Playground verification and limitations

Verified on Windows with Node `24.18.0` and npm `11.16.0`: plugin activation,
GutenStyle admin, Global preset save/reload, native Table editor controls,
Post/Block override persistence, and matching frontend output. The two-table
check produced radii of `16px` (Block) and `8px` (Global), with purple Post-level
headers on both. Both blocks remained valid; no browser errors or failed local
requests were recorded. Site settings and content also survived a stop/start.

The PHP debug log reports an existing GutenStyle early-translation-loading notice;
this environment setup does not change plugin initialization. First startup also
reported nonfatal SQLite `lockWholeFile: unlock failed` messages for database
protection files; saves and restart persistence passed afterward. Check the
printed site's `wp-content/debug.log` when diagnosing PHP problems. Verification
used the bundled default theme, not a full theme/browser or Docker/MySQL matrix.

### Docker / release QA

The existing `.wp-env.json` and `@wordpress/env` are retained for MySQL/Docker
verification. The configuration is Docker-oriented and has no `runtime` key.
With Docker Desktop running:

```bash
npm run env:start
npm start
```

wp-env uses its own data and the usual `admin` / `password` development login.
Playground's SQLite/PHP-WASM runtime is a fast development fallback, not a
replacement for release testing against normal WordPress hosting and MySQL.

Production build:

```bash
npm run build
```

Run the checks:

```bash
npm run typecheck
npm run lint
npm run test:unit
```

`npm run lint` includes JavaScript/TypeScript, CSS, PHP 7.4 syntax, and TypeScript checks. PHP syntax and engine unit tests use the pinned local PHP-WASM development runtime, so a system-wide PHP installation is not required.

Stop Docker WordPress (this does not stop Playground):

```bash
npm run env:stop
```

## Architecture

Read these first:

- `AGENTS.md`
- `docs/PRODUCT.md`
- `docs/ARCHITECTURE.md`
- `docs/DECISIONS.md`
- `docs/ROADMAP.md`

## Free / Pro

This repository is the public GutenStyle Free codebase.
GutenStyle Pro must live in a separate private repository and depend on the public extension APIs provided here.
