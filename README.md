# GutenStyle

GutenStyle is a visual styling system for native Gutenberg blocks.

## Development Environment

Requirements:

- Node.js 24.18.0 or newer in the Node 24 release line
- npm 11.16.0 or newer in the npm 11 release line
- Docker Desktop (the WSL2 backend is recommended on Windows)
- Git

Install the pinned dependencies:

```bash
npm ci
```

Start local WordPress:

```bash
npm run env:start
```

Start watch mode:

```bash
npm start
```

WordPress will normally be available at:

- http://localhost:8888
- Admin: http://localhost:8888/wp-admin/

Default wp-env credentials are typically:

- username: `admin`
- password: `password`

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

Stop WordPress:

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
