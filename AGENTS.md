# Negarity Color — agent guide

**Package:** `jalaljaberi/negarity-color`  
**Namespace:** `Negarity\Color\`  
**PHP:** ≥ 8.3  
**User docs:** Docusaurus in `docs/` (`npm run build` / `npm start`)

This library owns **color spaces, conversions, filters, extractors, generators, named colors, CIE helpers**. The WordPress plugin consumes it via Composer (`vendor/jalaljaberi/negarity-color`).

---

## Architecture

```text
Color / MutableColor
    │
    ├── ColorSpace\*          (RGB, Lab, LCh, XYZ, OkLab, Oklch, Luv, …)
    ├── CIE\*                 (illuminants, observers, CIECAM helpers)
    ├── Filter\* + FilterRegistry
    ├── Extractor\* + ExtractorRegistry
    ├── Generator\* + GeneratorRegistry
    └── Registry\*            (ColorSpaceRegistry, NamedColorRegistry, …)
```

### Core concepts

| Concept | Meaning |
|---------|---------|
| **Color space class** | Implements conversion to/from hub spaces (typically RGB / XYZ). Register in `ColorSpaceRegistry`. |
| **Immutable `Color`** | Default; `with` / `without` return new instances. |
| **`MutableColor`** | In-place mutations for fluent pipelines that need mutability. |
| **Filter** | Transforms a color (brightness, hue-rotate, grayscale, …). Registry-based. |
| **Extractor** | Returns a **scalar** (`float`/`string`) metric — brightness, CCT, contrast, etc. **Not** auto-registered. |
| **Generator** | Builds related colors / palettes (complementary, analogous, …). |
| **Strict clamping** | Optional; out-of-range channels may be allowed for intermediate conversion. |

### Conversion model

- Prefer conversions already implemented on color-space classes.
- Many paths hub through **RGB** and/or **XYZ** with CIE illuminant/observer context.
- New spaces should implement the same interface patterns as `Lab` / `LCh` / `OkLab`.

### Built-in extractors (registry names)

| Name | Class | Notes |
|------|-------|-------|
| `temperature` | `TemperatureExtractor` | McCamy / Planckian / Krystek |
| `brightness` | `BrightnessExtractor` | LCh L default + RGB / Rec. / CIECAM |
| `luminance` | `LuminanceExtractor` | CIE XYZ Y |
| `saturation` | `SaturationExtractor` | HSV default, HSL |
| `chroma` | `ChromaExtractor` | OKLCH default, Lab, Luv |
| `perceived_weight` | `PerceivedWeightExtractor` | Linear + multiplicative |
| `vibrancy` | `VibrancyExtractor` | Midtone chroma + Gaussian |
| `contrast` | `ContrastExtractor` | WCAG, Michelson, Weber, RMS, ΔE76 |

Each multi-algorithm extractor accepts `$params` as an array (`algorithm`, plus algorithm-specific keys). Legacy string params (e.g. contrast `'white'`/`'black'`) remain supported where documented.

---

## Agent rules

1. **Always register color spaces** (`ColorSpaceRegistry::registerBuiltIn()`) before creating colors in examples/tests.
2. **Register extractors/filters/generators** before `Registry::get(...)`.
3. New algorithm → constant + `resolveAlgorithmKey` aliases + `getAlgorithmLabel` + tests + `examples/Extractor/*.php` + `docs/docs/extractors-analysis/*.md` + update `references/extractors.md` and `introduction.md`.
4. Display scales for UI (0–100, WCAG 1–21, etc.) should stay consistent with existing extractors and WP sliders.
5. Avoid `*/` inside PHP docblocks (breaks parsing).
6. Prefer float channels; document ranges per space.
7. After library changes needed on the live site, sync to  
   `/var/www/negarity/wp-content/plugins/negarity-wp/vendor/jalaljaberi/negarity-color/`  
   (or run Composer update in the plugin).

---

## Tests & examples

```bash
./vendor/bin/phpunit
./vendor/bin/phpunit tests/ContrastExtractorTest.php
php examples/Extractor/Contrast.php
```

---

## Docs site

```bash
cd docs && npm install && npm run build   # production
cd docs && npm start                     # local; Node 20+ for Docusaurus
```

Public-facing analysis pages live under `docs/docs/extractors-analysis/`. Architecture for humans: [docs/docs/architecture.md](./docs/docs/architecture.md).

---

## Related

- Workspace: [../AGENTS.md](../AGENTS.md)
- Visualizer: [../negarity-color-visualizer/AGENTS.md](../negarity-color-visualizer/AGENTS.md)
- WP integration: `/var/www/negarity/wp-content/plugins/negarity-wp/AGENTS.md`
