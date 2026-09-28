# GutenStyle Product Specification

## Positioning
A visual styling and design system for native Gutenberg blocks.

## Core value
Create once -> inherit globally -> override per content item -> override per block -> preview visually -> reuse.

## Initial supported native blocks
- Heading
- Table
- List
- Image
- Accordion / Details
- Quote / Pullquote
- Buttons

## Editorial components roadmap
- TLDR
- Note
- Tip
- Warning
- Important
- Key Takeaway
- Steps
- Timeline
- Pros & Cons
- Checklist

## Scopes
1. Global
2. Post/Page/CPT
3. Individual block

The engine resolves these scopes property-by-property. A higher scope changes only values explicitly stored there. Reset removes an override so the lower scope becomes effective again. If GutenStyle has no value, Theme/Core remains responsible.

Presets participate in the same merge and do not replace the whole inherited style object. Interfaces may therefore show both the effective value's scope and whether a preset supplied it.

## Free
Must demonstrate the full core concept:
- Global styling
- Post-level styling
- Individual-block styling
- Live preview
- Basic presets
- Core native block support

## Pro direction
Monetize scale and advanced workflows:
- Bulk assignment
- Advanced/custom profiles
- Custom preset builder
- Advanced responsive controls
- Import/export
- Premium editorial components
- Agency workflows
- Role controls

The core registry, schemas, scope providers, resolver, compiler, storage contracts, and extension APIs remain Free. Pro consumes those public contracts as a separate add-on and does not ship a private replacement engine.

## Native Table V1

Table V1 proves the complete product flow with WordPress's native `core/table` block:

- Global Table enable/disable, visual preset choice, individual property overrides, per-property reset, whole-module reset, save state, and live preview.
- Post/Page/public block-editor CPT Table settings stored once in `_gutenstyle_styles`, affecting every Table in that content item without rewriting blocks.
- Individual Table Inspector settings stored in the generic `gutenstyle` block attribute, with effective preset/value origin and true inheritance resets.
- Frontend and editor output from the existing property resolver, native Style Engine compiler, and CSS-variable compiler.
- CSS-only horizontal scrolling that preserves native table semantics.

Supported V1 concerns are container border/style/width/color/radius/background; cell padding, text/background, and alignment; header background/text/weight/alignment; stripes, hover, and dividers; caption color/size/alignment/spacing; and native or horizontal-scroll responsive behavior.

Built-in Free presets are Theme Default, Clean, Bordered, Striped, Editorial, and Comparison. Presets define only GutenStyle-owned properties, so unspecified concerns continue inheriting.

Table V1 intentionally excludes sticky headers/columns, stacked mobile transformations, conditional columns, custom preset building, bulk application, and advanced responsive rules. Hover is decorative and never the only carrier of meaningful information.
