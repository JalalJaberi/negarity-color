# Negarity Color

Modern, extensible **color manipulation** library for **PHP 8.3+**.

```bash
composer require jalaljaberi/negarity-color
```

## Version and releases

The project version is **1.0.0**, recorded in [`VERSION`](./VERSION).
Use **`v1.0.0`** as the Git tag when publishing this release on GitHub.
Composer derives the package version from that tag.

The release workflow attaches `negarity-color-v1.0.0.zip` containing the runtime
code, production autoloader, license, and version file. See
[release instructions](./.github/RELEASING.md) before publishing.

## Quick links

| Resource | Location |
|----------|----------|
| Agent / contributor guide | [AGENTS.md](./AGENTS.md) |
| User documentation (Docusaurus) | [`docs/`](./docs/) |
| Architecture (docs site) | `docs/docs/architecture.md` |
| Examples | [`examples/`](./examples/) |
| Tests | `composer test` |

## Capabilities (summary)

- Color spaces: RGB(A), HSL(A), HSV, CMYK, Lab, LCh, XYZ, YCbCr, OkLab, Oklch, Luv
- Registries for spaces, named colors, filters, extractors, generators
- Extractors with multiple algorithms (temperature, brightness, contrast, …)
- Harmonic generators (complementary, analogous, triadic, …)

See [docs/README.md](./docs/README.md) for a longer usage sketch.

## License

MIT
