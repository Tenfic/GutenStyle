# GutenStyle Architecture

## Style Engine data flow

```text
Registration hooks
        |
        v
StyleRegistry (properties, presets, block adapters)
        |
        v
Registry-backed validation and sanitization
        |
        v
Global option ---- Post meta ---- Block attributes
        |               |               |
        +------ scope providers --------+
                        |
                        v
               StyleResolver
                        |
                        v
 EffectiveStyle (value + Global/Post/Block origin per property)
              /                         \
 WordPress Style Engine path      CSS custom-property compiler
 (when an adapter maps it)        (registered safe mappings only)
```

`StyleEngine::resolve( $block_name, $post_id, $block_attributes )` is the high-level entry point. It assembles the three providers in the required order so block modules do not duplicate resolution logic.

## Property schema

`PropertyDefinition` describes a reusable property ID, label, type, constraints, default behavior, responsive capability, supported modules, and optional CSS-variable mappings. Initial types are boolean, enum, hexadecimal color, bounded number, safe dimension, and constrained string. Raw CSS is not a property type.

Definitions are registered before adapters and presets. A preset is rejected unless every value passes the same property schema and is supported by its adapter.

## Resolution

The cascade is:

`Theme/Core < GutenStyle Global < Post/Page/CPT < Individual Block`

Each scope contributes only the properties it explicitly stores. Preset properties are expanded into that same merge; an explicit value in the same scope overrides its preset value. The result records `source` (`theme`, `global`, `post`, or `block`), `via` (`none`, `preset`, or `property`), and the preset ID where relevant.

Theme-owned properties remain unresolved with a `null` value. The engine does not invent a value merely because a property is registered.

## Storage

- Global: versioned `gutenstyle_global_styles` option, stored with autoload disabled.
- Post/Page/CPT: versioned `_gutenstyle_styles` registered post meta for public post types that support the block editor. It is exposed through the native REST entity schema and uses `edit_post` authorization.
- Individual block: versioned `gutenstyle` block attribute object. It does not replace the native block or rewrite its content.

Documents use this shape:

```json
{
  "version": 1,
  "blocks": {
    "namespace/block": {
      "version": 1,
      "preset": "example-clean",
      "properties": {
        "radius": "8px"
      }
    }
  }
}
```

The block attribute stores the inner scope object directly. Resetting a property removes its key; resetting a scope removes its empty object. Inherited values are never copied upward, so later Global or Post changes continue to flow through.

## Output boundary

`BlockAdapterInterface` declares supported properties and may map each one to either:

- a future native WordPress Style Engine path, or
- a `--gutenstyle-*` custom property.

`CssVariableCompiler` emits only resolved, schema-valid values for registered custom-property names. Selectors and block-specific CSS remain the adapter's responsibility. This keeps native WordPress handling available without coupling every property to custom CSS.

## REST

- `GET /gutenstyle/v1/schema`: registered properties, presets, and adapter mappings; requires `edit_posts`.
- `GET /gutenstyle/v1/presets`: preset definitions; requires `edit_posts`.
- `GET /gutenstyle/v1/styles/global`: Global document; requires `edit_posts`.
- `PUT /gutenstyle/v1/styles/global`: strict validated Global update; requires `manage_options`.

Post styles use registered native post meta rather than a duplicate custom write route.

## Bulk operations
Bulk is not another cascade layer.
Bulk assigns profiles/settings to selected content instead of rewriting every block.

## Extension model
Free exposes stable hooks/contracts.
Pro behaves like a third-party extension.

Current registration hooks, in order:

- `gutenstyle/register_style_properties`
- `gutenstyle/register_block_adapters`
- `gutenstyle/register_style_presets`
- `gutenstyle/register_styles` (backward-compatible general hook)

The public `gutenstyle_style_registry()` and `gutenstyle_style_engine()` functions expose the booted Free services. Registry contracts also reserve generic component and profile registrations for later milestones. Pro must register through these Free contracts rather than copy or replace the engine.

## Modules
Each supported block is an adapter/module.
Adding a block must not require rewriting Global, Post, Block, REST, or import/export systems.

Milestone 1 intentionally registers no product block adapter. The first real adapter belongs to Milestone 2 after this engine is reviewed.

## Performance posture

Global data is one non-autoloaded option; a resolver request reads only the requested post meta and block attributes. Registry definitions are in-memory for the request. The first implementation avoids premature persistent caches, but the provider and compiler boundaries allow caching later without changing module contracts.
