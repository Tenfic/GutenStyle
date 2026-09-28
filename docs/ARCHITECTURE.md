# GutenStyle Architecture

## Core layers

Style Registry
-> Style Resolver
-> Scope providers (Global / Post / Block)
-> Effective property map
-> Native WordPress Style Engine where possible
-> GutenStyle CSS variable/compiler fallback where needed

## Storage
- Global: WordPress Options API
- Post/Page/CPT: registered post meta
- Individual block: block attributes / stable classes where necessary

## Bulk operations
Bulk is not another cascade layer.
Bulk assigns profiles/settings to selected content instead of rewriting every block.

## Extension model
Free exposes stable hooks/contracts.
Pro behaves like a third-party extension.

Planned public extension points:
- register preset
- register block adapter
- register property schema
- register component
- register profile
- resolved-style filters

## Modules
Each supported block is an adapter/module.
Adding a block must not require rewriting Global, Post, Block, REST, or import/export systems.
