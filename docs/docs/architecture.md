---
title: Architecture
sidebar_position: 2
---

# Architecture

This page summarizes how **Negarity Color** is structured for contributors and AI agents. For coding rules and contribution conventions, see **`AGENTS.md`** at the package root.

## Layers

1. **Value objects** — `Color` (immutable) and `MutableColor` hold a color-space class + channel map + optional CIE illuminant/observer.
2. **Color spaces** — classes under `Negarity\Color\ColorSpace` know channel names, defaults, and conversions. Register via `ColorSpaceRegistry`.
3. **CIE support** — illuminants, observers, and appearance helpers (e.g. CIECAM) under `Negarity\Color\CIE`.
4. **Registries** — pluggable catalogs for spaces, named colors, filters, extractors, and generators. Extractors/filters/generators are **opt-in** (not auto-registered).
5. **Filters** — unary (or parameterized) transforms that return a new color.
6. **Extractors** — map a color (+ optional params) to a scalar for UI, accessibility, or analytics.
7. **Generators** — produce related colors / palettes from a seed color.

## Design principles

- **Chainable** — prefer APIs that compose (`with`, filters, generators).
- **Extensible** — new spaces and extractors should plug into registries without editing core conversion hubs unnecessarily.
- **Float precision** — store and convert with floats; clamp only when the API/strict mode requires it.
- **Documented algorithms** — multi-algorithm extractors expose stable string keys, constants, aliases, and human labels.

## Typical call flow (extractor)

```text
ColorSpaceRegistry::registerBuiltIn()
ExtractorRegistry::register(new FooExtractor())
$color = Color::rgb(...)
$value = ExtractorRegistry::get('foo')->extract($color, ['algorithm' => '...'])
FooExtractor::getLabelForValue($value)
```

## WordPress consumption

The Negarity WP plugin depends on this package via Composer. Interactive calculators:

1. Call REST controllers that build a `Color` and run extractors.
2. Render results with shared frontend helpers and optional calculator icons.
3. Theme shortcodes may hydrate property extractors from the URL color.

When adding an extractor algorithm used on the site, update this library **and** the plugin block/REST layer.
