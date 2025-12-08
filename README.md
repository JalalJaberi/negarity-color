## Architecture Metrics
### Chainable: Always keep it possible to chain actions like $color->lighten()->extractChannel('r')->contrastRatio($blue)...
### Extensible: So the developer can add more color spaces, more named color sets, filters, ...

## todos:
### Add unit tests
### Add more named colors (web, ...)
### move the constant values like color names, color space names, etc. to a file as constants
### add some helper functions like rgbtohex(r, g, b) to work as single functions\
### add palletes as a feature
### add generators: random color generator, random pallete generator, pallet generator having some info, ...
### Add filters and modifiers
 - Brighten / darken (absolute and relative).
 - Lighten / darken via HSL lightness.
 - Saturate / desaturate, contrast, invert.
 - Grayscale (multiple algorithms: average, luminosity, desaturate).
 - Hue rotate, temperature/tint (warm/cool shifts).
 - Channel-specific masks and adjustments (e.g., zero green, keep red only).
 - Predefined filters: sepia, posterize, threshold, gamma.
### Add global functions like rgbTocmyk()