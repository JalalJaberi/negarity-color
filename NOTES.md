## Architecture Metrics
### Chainable: Always keep it possible to chain actions like $color->lighten()->extractChannel('r')->contrastRatio($blue)...
### Extensible: So the developer can add more color spaces, more named color sets, filters, ...

## todos:
### Add unit tests
### more flexibility for channel values (use float and percentage)
### more flexibility to convert from string (percentage values, float values, regex)
### Add more named colors (web, ...)
 - https://people.csail.mit.edu/jaffer/Color/Dictionaries#The%20Dictionaries
### add some helper functions like rgbtohex(r, g, b) to work as standalone functions
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
### Think about moving convertets like toRgb to separate classes or color space classes
### Use ColorSpaceRegistry as we did for filters and color names.
### Support for sRGB and optional color profiles (display-p3) as opt-in.
### Add more filters (invert, sepia, etc)
### Add these to analyzers
- equals() and almostEquals() with epsilon or ΔE tolerance.
- Perceptual distance: Delta E metrics (ΔE76, ΔE00) if Lab supported.
- luminance() (relative luminance), isLight() / isDark() (WCAG-friendly threshold).
- Contrast ratio and contrast checking (WCAG 2.1 thresholds).
### Add these to generators/extractors
- complement(), triadic(), analogous() color palette helpers.
- average() / mean() of multiple colors (weighted).
### use toInt() / toPackedInt() and fromPackedInt() for fast storage. for performance
### toCssVariable() helper
### Add more exceptions to the code and complete exception codes
### Move converters to Color spaces classes, so each color space knows how to convert to/from another color space. Like this, it will be easier to add custom color spaces and convert to/from it.
### There are color models for XYZ color space (LMS, xyY, YUV). We can also consider them.
### Can we create estimation for colors which are in one color space nut not in the other one?
### We can also consider creating Positive XYZ color space
### RGB has a model ACEScg / ACES2065-1
### consider CIELAB and CIELUV color spaces
