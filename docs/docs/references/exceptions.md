# Exceptions Reference

This library uses a comprehensive exception hierarchy to provide clear error messages and help with debugging. All exceptions implement the `ColorExceptionInterface` marker interface.

## Exception Hierarchy

```
\Exception
├── \InvalidArgumentException
│   └── InvalidColorValueException
│   └── InvalidFormatException
└── \RuntimeException
    ├── ColorSpaceNotFoundException
    ├── ConversionNotSupportedException
    ├── FilterNotFoundException
    ├── NamedColorNotFoundException
    └── UnsupportedColorSpaceException
```

## Exception Types

### InvalidColorValueException

**Extends:** `\InvalidArgumentException`  
**Implements:** `ColorExceptionInterface`

Thrown when a color channel value is invalid (out of range, wrong type, etc.).

**Common scenarios:**
- Channel value is outside the valid range (e.g., RGB channel > 255)
- Channel value is not numeric
- Channel does not exist in the color space

**Example:**
```php
try {
    $color = Color::rgb(300, 100, 50); // R channel out of range
} catch (InvalidColorValueException $e) {
    echo $e->getMessage();
    // Output: Channel "r" must be between 0.00 and 255.00, got 300.00
}
```

**Note:** In non-strict clamping mode, out-of-range values are allowed and clamped only on output, so this exception may not be thrown during construction.

---

### InvalidFormatException

**Extends:** `\InvalidArgumentException`  
**Implements:** `ColorExceptionInterface`

Thrown when a color format string is invalid or malformed.

**Common scenarios:**
- Hex string has invalid length
- Hex string contains invalid characters
- Color string format is malformed

**Example:**
```php
try {
    $color = Color::hex('GG'); // Invalid hex format
} catch (InvalidFormatException $e) {
    echo $e->getMessage();
    // Output: Hex value must be 3 (rgb), 4 (rgba), 6 (rrggbb), or 8 (rrggbbaa) characters long. Got 2 characters.
}
```

---

### ColorSpaceNotFoundException

**Extends:** `\RuntimeException`  
**Implements:** `ColorExceptionInterface`

Thrown when a requested color space is not registered in the registry.

**Common scenarios:**
- Attempting to convert to an unregistered color space
- Using a color space factory method before registering built-in color spaces
- Accessing a color space that was never registered

**Example:**
```php
try {
    // Forgot to register color spaces
    $color = Color::rgb(255, 100, 50);
} catch (ColorSpaceNotFoundException $e) {
    echo $e->getMessage();
    // Output: Color space 'rgb' not registered.
}

// Solution: Register color spaces first
ColorSpaceRegistry::registerBuiltIn();
$color = Color::rgb(255, 100, 50); // Now works
```

---

### ConversionNotSupportedException

**Extends:** `\RuntimeException`  
**Implements:** `ColorExceptionInterface`

Thrown when a color space conversion is not supported or cannot be performed.

**Common scenarios:**
- No direct conversion method exists between two color spaces
- RGB conversion chain fails
- Required conversion methods are missing

**Example:**
```php
try {
    $lab = Color::lab(50, 20, -30);
    $cmyk = $lab->toCMYK(); // If conversion chain fails
} catch (ConversionNotSupportedException $e) {
    echo $e->getMessage();
    // Output: Conversion from 'lab' to 'cmyk' is not supported. 
    //         Neither direct conversion methods nor RGB conversion chain are available.
    //         Attempted methods: Direct method 'toCmyk' not found; Reverse method 'fromLab' not found; RGB conversion chain failed: ...
}
```

**Note:** The exception message includes details about which conversion methods were attempted, helping with debugging.

---

### FilterNotFoundException

**Extends:** `\RuntimeException`  
**Implements:** `ColorExceptionInterface`

Thrown when a requested filter is not registered in the filter registry.

**Common scenarios:**
- Using a filter that hasn't been registered
- Typo in filter name
- Filter was unregistered

**Example:**
```php
try {
    $color = Color::rgb(255, 100, 50);
    $filtered = $color->brightness(20); // If brightness filter not registered
} catch (FilterNotFoundException $e) {
    echo $e->getMessage();
    // Output: Filter 'brightness' not registered.
}

// Solution: Register filters first
FilterRegistry::registerBuiltIn();
```

---

### NamedColorNotFoundException

**Extends:** `\RuntimeException`  
**Implements:** `ColorExceptionInterface`

Thrown when a named color is not found in a named color registry.

**Common scenarios:**
- Requested named color doesn't exist in the registry
- Named color exists but not for the requested color space
- Registry method called without checking if color exists first

**Example:**
```php
try {
    $registry = new VGANamedColors();
    $values = $registry->getColorValuesByName('nonexistent', RGB::class);
} catch (NamedColorNotFoundException $e) {
    echo $e->getMessage();
    // Output: Named color 'nonexistent' not found in registry.
}
```

---

### UnsupportedColorSpaceException

**Extends:** `\RuntimeException`  
**Implements:** `ColorExceptionInterface`

Thrown when a color space operation is attempted on a color space that doesn't support it.

**Common scenarios:**
- Attempting to change illuminant on RGB color (RGB doesn't support illuminants)
- Attempting to change observer on HSL color (HSL doesn't support observers)
- Using CIE-specific methods on non-CIE color spaces

**Example:**
```php
try {
    $rgb = Color::rgb(255, 100, 50);
    $adapted = $rgb->adaptIlluminant(CIEIlluminant::D50); // RGB doesn't support illuminants
} catch (UnsupportedColorSpaceException $e) {
    echo $e->getMessage();
    // Output: Color space 'rgb' does not support illuminants.
}

// Solution: Use a CIE color space
$lab = Color::lab(50, 20, -30);
$adapted = $lab->adaptIlluminant(CIEIlluminant::D50); // Works!
```

---

## Exception Handling Best Practices

### 1. Register Required Components

Always register color spaces and filters before use:

```php
use Negarity\Color\Registry\ColorSpaceRegistry;
use Negarity\Color\Filter\FilterRegistry;

// Register built-in components
ColorSpaceRegistry::registerBuiltIn();
FilterRegistry::registerBuiltIn();
```

### 2. Check Before Converting

For custom color spaces, check if conversion is supported:

```php
if (ColorSpaceRegistry::has('custom-space')) {
    try {
        $converted = $color->toCustomSpace();
    } catch (ConversionNotSupportedException $e) {
        // Handle conversion failure
    }
}
```

### 3. Handle CIE Operations

Check if color space supports CIE operations before using them:

```php
if ($color->getColorSpace()::supportsIlluminant()) {
    $adapted = $color->adaptIlluminant(CIEIlluminant::D50);
} else {
    // Convert to a CIE color space first
    $lab = $color->toLab();
    $adapted = $lab->adaptIlluminant(CIEIlluminant::D50);
}
```

### 4. Validate Input Formats

Validate hex strings and other formats before use:

```php
function createColorFromHex(string $hex): ?Color {
    try {
        return Color::hex($hex);
    } catch (InvalidFormatException $e) {
        error_log("Invalid hex format: " . $e->getMessage());
        return null;
    }
}
```

---

## Logging and Debugging

The library logs conversion attempts to help with debugging. When a conversion fails, check your error logs for messages like:

```
[Negarity Color] Conversion attempt failed: lab -> cmyk via direct method. Error: ...
[Negarity Color] Conversion attempt failed: lab -> cmyk via reverse method. Error: ...
[Negarity Color] Conversion attempt failed: lab -> cmyk via RGB chain. Error: ...
```

These logs help identify which conversion paths were attempted and why they failed.

---

## Related Documentation

- [Creating Colors](../basics/creating-colors.md) - How to create colors and handle creation errors
- [Converting Colors](../basics/converting-colors.md) - Color space conversion and conversion errors
- [Color Spaces Reference](./color-spaces.md) - Supported color spaces and their requirements
