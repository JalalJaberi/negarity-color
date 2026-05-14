## Architecture Metrics
### Chainable: Always keep it possible to chain actions like $color->lighten()->extractChannel('r')->contrastRatio($blue)...
### Extensible: So the developer can add more color spaces, more named color sets, filters, ...

## todos:
### add extractors to docs (and later link to them in the website)
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
- also implement different temperature formulas/calculation models
### use toInt() / toPackedInt() and fromPackedInt() for fast storage. for performance
### toCssVariable() helper
### Add more exceptions to the code and complete exception codes
### Move converters to Color spaces classes, so each color space knows how to convert to/from another color space. Like this, it will be easier to add custom color spaces and convert to/from it.
### There are color models for XYZ color space (LMS, xyY, YUV). We can also consider them.
### Can we create estimation for colors which are in one color space nut not in the other one?
### We can also consider creating Positive XYZ color space
### RGB has a model ACEScg / ACES2065-1
### consider CIELAB and CIELUV color spaces



##Libraries to look inside for inspiration
### spatie/color
### matthieumastadenis/couleur
### ramazancetinkaya/color-code-converter

### new hierarchy of color models
Exactly — now we can use **dependency + subset** locally: a model can go under its parent **if it depends on it and has a smaller gamut**, even if the parent itself is not a subset of XYZ. Let’s construct the full hierarchy cleanly.

* **CIE XYZ**

  * **RGB** (subset, dependent on XYZ)

    * **CMY** (subset, dependent on RGB)

      * **CMYK** (subset, dependent on CMY)
    * **YPbPr** (subset, dependent on RGB)

      * **YCbCr** (subset, dependent on YPbPr)
      * **YUV** (subset, dependent on YPbPr)
      * **YIQ** (subset, dependent on YPbPr)
    * **HSV** (subset, dependent on RGB)
    * **HSL** (subset, dependent on RGB)
    * **HSI** (subset, dependent on RGB)
    * **HWB** (subset, dependent on RGB)
* **CIE Lab** (dependent on XYZ, not a subset, root for its own derivatives)

  * **LCH** (subset, dependent on Lab)
  * **Hunter Lab** (subset, dependent on Lab)
  * **DIN99** (subset, dependent on Lab)
* **CIE Luv** (dependent on XYZ, not a subset)
* **OKLab** (dependent on XYZ, not a subset)

  * **OKLCH** (subset, dependent on OKLab)
* **IPT** (dependent on LMS, not a subset)
* **LMS** (dependent on XYZ, not a subset)

  * **Opponent Color Model** (subset, dependent on LMS)
* **Munsell** (perceptual, independent)
* **NCS** (perceptual, independent)

✅ Notes:

* Subsets are indented only if **both dependency + smaller gamut** criteria are met.
* Perceptual reinterpretations (Lab, Luv, OKLab, Munsell, NCS) act as roots for their own subsets.
* Models like HSV/HSL are considered subsets of RGB because they cover the same RGB gamut in a geometric mapping.


Absolutely — below is a **global taxonomy of color models and color spaces** using your **“dependent + subset”** relation, and **clearly distinguishing between**:

* 🔹 **Color Model** — abstract mathematical representation of color components, independent of specific primaries/parameters
* 🔸 **Color Space** — a concrete instantiation of a model that *defines specific primaries, white points, ranges, and gamut*

Color spaces are listed under their respective model as **children**.

---

## 🎨 Full Hierarchy: Color Models & Color Spaces (dependent + subset)

* **CIE XYZ (model)**

  * 🔸 *XYZ D50 (space)* ([MDN Web Docs][1])
  * 🔸 *XYZ D65 (space)* ([MDN Web Docs][1])
  * **RGB (model)**

    * 🔸 *sRGB* ([MDN Web Docs][1])
    * 🔸 *scRGB* ([Wikipedia][2])
    * 🔸 *Adobe RGB (1998)* ([MDN Web Docs][1])
    * 🔸 *ProPhoto RGB* ([MDN Web Docs][1])
    * 🔸 *Display‑P3* ([MDN Web Docs][1])
    * 🔸 *Rec. 709/BT.709* ([Wikipedia][3])
    * 🔸 *Rec. 2020* ([MDN Web Docs][1])
    * **CMY (model)**

      * 🔸 *CMYK (space)* ([aloso.github.io][4])
    * **YPbPr (model)**

      * 🔸 *YCbCr (space)* ([docs.color-core.com][5])
      * 🔸 *YUV (space)* ([docs.color-core.com][5])
      * 🔸 *YIQ (space)* ([docs.color-core.com][5])
    * 🔸 *HSV (space)* ([MDN Web Docs][1])
    * 🔸 *HSL (space)* ([MDN Web Docs][1])
    * 🔸 *HSI (space)* ([docs.color-core.com][5])
    * 🔸 *HWB (space)* ([MDN Web Docs][1])
  * **LMS (model)**

    * **Opponent Color Model (model)**

      * 🔸 *Various opponent‑derived spaces* (e.g., certain YCbCr variants) ([docs.color-core.com][5])

* **CIE Lab (model)**

  * 🔸 *Lab D50 (space)* ([MDN Web Docs][1])
  * 🔸 *Lab D65 (space)* ([MDN Web Docs][1])
  * **LCH (model/space)** (cylindrical variant of Lab) ([MDN Web Docs][1])
  * **Hunter Lab (model/space)** ([docs.color-core.com][5])
  * **DIN99 (model/space)** ([docs.color-core.com][5])

* **CIE Luv (model)**

  * 🔸 *Luv (space)* ([aloso.github.io][4])

* **OKLab (model)**

  * 🔸 *OKLab (space)* ([culorijs.org][6])
  * 🔸 *OKLCH (space)* ([culorijs.org][6])
  * 🔸 *Okhsl / Okhsv / Okhwb* (cylindrical variants) ([culorijs.org][6])

* **IPT (model/space)** ([docs.color-core.com][5])

* **Perceptual / Appearance Models (independent root models)**

  * **Munsell (model)**

    * 🔸 *Munsell HVC (space)* ([Unicolour][7])
  * **NCS — Natural Color System (model)**

    * 🔸 *NCS spaces* (various NCS specifications) (not exhaustively listed online)

---

## 🧠 Notes on This Taxonomy

* **Models** are **bold** (abstract).
* **Spaces** are **bulleted with 🔸** under the model they concretize.
* Some cylindrical perceptual spaces (like LCH and OKLCH) are treated as distinct *spaces* or *sub‑models* because they reorder dimensions but share gamut with their base model. ([MDN Web Docs][1])
* Many additional specialized spaces exist (e.g., ACES, ProPhoto variants, film‑specific profiles); what’s included here are widely recognized standards. ([rxlab.guide][8])

---

## 🔍 Clarifications

* A **color model** becomes a **color space** only when **primaries, transfer functions, white points, gamuts, and numeric ranges** are defined. ([MDN Web Docs][1])
* The taxonomy shows **dependency + subset**: e.g., all RGB spaces are subsets within the RGB model, which itself is a subset of the visible gamut represented by XYZ.
* Perceptual models like Lab or OKLab are derived from XYZ but aren’t subsets in the gamut sense — their spaces are the actual representations used in practice. ([MDN Web Docs][1])

---

[1]: https://developer.mozilla.org/docs/Glossary/Color_space?utm_source=chatgpt.com "Color space - Glossary | MDN"
[2]: https://en.wikipedia.org/wiki/ScRGB?utm_source=chatgpt.com "ScRGB"
[3]: https://en.wikipedia.org/wiki/RGB_color_spaces?utm_source=chatgpt.com "RGB color spaces"
[4]: https://aloso.github.io/colo/color_spaces.html?utm_source=chatgpt.com "Color spaces | colo"
[5]: https://docs.color-core.com/color-spaces?utm_source=chatgpt.com "Color Spaces in color-core | My Site"
[6]: https://culorijs.org/color-spaces/?utm_source=chatgpt.com "Color Spaces · culori"
[7]: https://unicolour.wacton.xyz/?utm_source=chatgpt.com "Unicolour | 🌈 Colour / Color conversion, interpolation, and comparison for .NET"
[8]: https://rxlab.guide/colors/space-list.html?utm_source=chatgpt.com "Selective list of color spaces - RxDocs - Color Management"
