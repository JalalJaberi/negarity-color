<?php

declare(strict_types=1);

namespace Negarity\Color;

use Negarity\Color\ColorSpace\{
    ColorSpaceInterface,
    RGB,
    RGBA,
    CMYK,
    HSL,
    HSLA,
    HSV,
    Lab,
    LCh,
    XYZ,
    YCbCr
};
use Negarity\Color\Registry\NamedColorRegistryInterface;
use Negarity\Color\Registry\ColorSpaceRegistry;
use Negarity\Color\Filter\{
    FilterRegistry,
    Unary\UnaryColorFilterInterface,
    Parameterized\ParameterizedColorFilterInterface,
    Binary\BinaryColorFilterInterface
};
use Negarity\Color\CIE\CIEIlluminant;
use Negarity\Color\CIE\CIEObserver;
use Negarity\Color\CIE\CIEIlluminantData;
use Negarity\Color\CIE\AdaptationMethod;
use Negarity\Color\Exception\ColorSpaceNotFoundException;
use Negarity\Color\Exception\ConversionNotSupportedException;
use Negarity\Color\Exception\InvalidFormatException;
use Negarity\Color\Exception\UnsupportedColorSpaceException;
use Negarity\Color\Exception\FilterNotFoundException;
use Negarity\Color\Exception\InvalidColorValueException;

abstract class AbstractColor implements \JsonSerializable, ColorInterface
{
    /**
     * Strict clamping mode (class default).
     * When true (default), values are clamped on assignment/operations.
     * When false, original values are kept and clamped only on output.
     */
    public const bool STRICT_CLAMPING = true;

    /** @var NamedColorRegistryInterface[] */
    private static array $registries = [];
    /** @var class-string<ColorSpaceInterface> */
    protected string $colorSpace;
    /** @var array<string, float> Always stores original values (never clamped) */
    protected array $values = [];
    /** @var bool Instance-level strict clamping mode (overrides class constant) */
    protected bool $strictClamping;
    /** @var CIEIlluminant */
    protected CIEIlluminant $illuminant;
    /** @var CIEObserver */
    protected CIEObserver $observer;

    /**
     * Constructor.
     * 
     * @param class-string<ColorSpaceInterface> $colorSpace
     * @param array<string, float|int> $values Accepts int|float for convenience, stores as float
     * @param CIEIlluminant|null $illuminant CIE standard illuminant (default: D65)
     * @param CIEObserver|null $observer CIE standard observer (default: TwoDegree)
     * @param bool|null $strictClamping Override strict clamping mode (null = use class constant)
     * @throws \InvalidArgumentException
     */
    public function __construct(
        string $colorSpace, 
        array $values = [],
        ?CIEIlluminant $illuminant = null,
        ?CIEObserver $observer = null,
        ?bool $strictClamping = null
    ) {
        if (!is_subclass_of($colorSpace, ColorSpaceInterface::class)) {
            throw new \InvalidArgumentException(
                "$colorSpace must implement ColorSpaceInterface"
            );
        }

        $this->colorSpace = $colorSpace;
        $this->illuminant = $illuminant ?? CIEIlluminant::D65;
        $this->observer = $observer ?? CIEObserver::TwoDegree;
        $this->strictClamping = $strictClamping ?? static::STRICT_CLAMPING;

        $colorSpaceChannels = $colorSpace::getChannels();

        foreach ($colorSpaceChannels as $name) {
            if (!isset($values[$name])) {
                $defaultValue = $colorSpace::getChannelDefaultValue($name);
                // Always store original values (defaults are already in valid range)
                $this->values[$name] = $defaultValue;
            } else {
                $value = $values[$name];
                $type = gettype($value);
                if ($type !== 'integer' && $type !== 'double' && $type !== 'float') {
                    throw new \InvalidArgumentException("Channel '{$name}' must be of type int or float. It's type is '{$type}'.");
                }
                // Convert to float and always store original value (never clamp in storage)
                // Type validation already done above (int/float check)
                // Range validation: In strict mode we clamp on-the-fly, in non-strict we allow out-of-range
                $floatValue = (float)$value;
                $this->values[$name] = $floatValue;
            }
        }
    }

    /**
     * Add a named color registry.
     * 
     * @param NamedColorRegistryInterface $registry
     * @return void
     */
    public static function addRegistry(NamedColorRegistryInterface $registry): void
    {
        static::$registries[] = $registry;
    }

    /**
     * Remove a named color registry.
     * 
     * @param NamedColorRegistryInterface $registry
     * @return void
     */
    public static function removeRegistry(NamedColorRegistryInterface $registry): void
    {
        $key = array_search($registry, static::$registries, true);
        if ($key !== false) {
            unset(static::$registries[$key]);
            static::$registries = array_values(static::$registries); // Reindex array
        }
    }

    #[\Override]
    final public function getColorSpace(): string
    {
        return $this->colorSpace;
    }

    #[\Override]
    final public function getColorSpaceName(): string
    {
        return $this->colorSpace::getName();
    }

    #[\Override]
    final public function getChannels(): array
    {
        return $this->colorSpace::getChannels();
    }

    #[\Override]
    final public function getChannel(string $name): float
    {
        if (!in_array($name, $this->getChannels(), true)) {
            throw new \InvalidArgumentException("Channel '{$name}' does not exist in color space '{$this->getColorSpaceName()}'.");
        }
        
        // Always return clamped value for safety (for display/output)
        // $values always contains original values, so we clamp them on-the-fly
        return $this->colorSpace::clampValue($name, $this->values[$name]);
    }

    /**
     * Get the raw (original) channel value without clamping.
     * Always returns the original value as it was set, regardless of clamping mode.
     * 
     * @param string $name The channel name.
     * @return float The raw channel value.
     */
    final public function getChannelRaw(string $name): float
    {
        if (!in_array($name, $this->getChannels(), true)) {
            throw new \InvalidArgumentException("Channel '{$name}' does not exist in color space '{$this->getColorSpaceName()}'.");
        }
        
        // $values always contains original values
        return $this->values[$name];
    }

    /**
     * Get channel value for operations (used by filters and internal conversions).
     * In strict mode: returns clamped value (clamps on-the-fly from original).
     * In non-strict mode: returns original value (from $values).
     * 
     * @param string $name The channel name.
     * @return float The channel value appropriate for operations.
     */
    final public function getChannelForCalculation(string $name): float
    {
        if ($this->strictClamping) {
            // Strict mode: clamp on-the-fly for operations
            return $this->colorSpace::clampValue($name, $this->values[$name]);
        }
        // Non-strict mode: use original values for operations
        return $this->values[$name];
    }

    /**
     * Get the CIE standard illuminant for this color.
     * 
     * @return CIEIlluminant
     */
    final public function getIlluminant(): CIEIlluminant
    {
        return $this->illuminant;
    }

    /**
     * Get the CIE standard observer for this color.
     * 
     * @return CIEObserver
     */
    final public function getObserver(): CIEObserver
    {
        return $this->observer;
    }

    /**
     * Create a new color instance with a different illuminant (metadata only, no conversion).
     * 
     * @param CIEIlluminant $illuminant The new illuminant
     * @return static
     * @throws UnsupportedColorSpaceException If the color space does not support illuminants
     */
    abstract public function withIlluminant(CIEIlluminant $illuminant): static;

    /**
     * Adapt the color to a different illuminant using chromatic adaptation.
     * 
     * This method performs chromatic adaptation to make the color appear the same
     * under a different illuminant. The color values are converted accordingly.
     * 
     * @param CIEIlluminant $targetIlluminant The target illuminant
     * @param \Negarity\Color\CIE\AdaptationMethod|null $method The adaptation method (default: Bradford)
     * @return static
     * @throws \RuntimeException If the color space does not support illuminants
     */
    abstract public function adaptIlluminant(
        CIEIlluminant $targetIlluminant,
        ?\Negarity\Color\CIE\AdaptationMethod $method = null
    ): static;

    /**
     * Adapt the color to a different observer.
     * 
     * This method converts the color values to account for the different observer's
     * color matching functions (2° vs 10°).
     * 
     * @param CIEObserver $targetObserver The target observer
     * @return static
     * @throws UnsupportedColorSpaceException If the color space does not support observers
     */
    abstract public function adaptObserver(CIEObserver $targetObserver): static;

    /**
     * Perform chromatic adaptation on XYZ values.
     * 
     * This is a helper method that performs the actual chromatic adaptation math.
     * It converts XYZ values from one illuminant to another using the specified method.
     * 
     * @param array{x: float, y: float, z: float} $xyz Source XYZ values
     * @param CIEIlluminant $sourceIlluminant Source illuminant
     * @param CIEIlluminant $targetIlluminant Target illuminant
     * @param CIEObserver $observer Observer (for getting reference white)
     * @param AdaptationMethod $method Adaptation method to use
     * @return array{x: float, y: float, z: float} Adapted XYZ values
     */
    protected function performChromaticAdaptation(
        array $xyz,
        CIEIlluminant $sourceIlluminant,
        CIEIlluminant $targetIlluminant,
        CIEObserver $observer,
        AdaptationMethod $method
    ): array {
        // Get reference white (XYZ tristimulus) for both illuminants
        $sourceWhite = CIEIlluminantData::getXYZ($sourceIlluminant, $observer);
        $targetWhite = CIEIlluminantData::getXYZ($targetIlluminant, $observer);

        // If same illuminant, no adaptation needed
        if ($sourceIlluminant === $targetIlluminant) {
            return $xyz;
        }

        // Perform adaptation based on method
        return match ($method) {
            AdaptationMethod::Bradford => $this->bradfordAdaptation($xyz, $sourceWhite, $targetWhite),
            AdaptationMethod::VonKries => $this->vonKriesAdaptation($xyz, $sourceWhite, $targetWhite),
            AdaptationMethod::XYZScaling => $this->xyzScalingAdaptation($xyz, $sourceWhite, $targetWhite),
        };
    }

    /**
     * Bradford chromatic adaptation transform.
     * 
     * @param array{x: float, y: float, z: float} $xyz Source XYZ
     * @param array{x: float, y: float, z: float} $sourceWhite Source reference white
     * @param array{x: float, y: float, z: float} $targetWhite Target reference white
     * @return array{x: float, y: float, z: float} Adapted XYZ
     */
    private function bradfordAdaptation(array $xyz, array $sourceWhite, array $targetWhite): array
    {
        // Bradford transformation matrix
        $M = [
            [0.8951, 0.2664, -0.1614],
            [-0.7502, 1.7135, 0.0367],
            [0.0389, -0.0685, 1.0296]
        ];

        // Convert source XYZ to cone response domain (RGB)
        $sourceRgb = [
            $M[0][0] * $xyz['x'] + $M[0][1] * $xyz['y'] + $M[0][2] * $xyz['z'],
            $M[1][0] * $xyz['x'] + $M[1][1] * $xyz['y'] + $M[1][2] * $xyz['z'],
            $M[2][0] * $xyz['x'] + $M[2][1] * $xyz['y'] + $M[2][2] * $xyz['z']
        ];

        // Convert reference whites to cone response domain
        $sourceWhiteRgb = [
            $M[0][0] * $sourceWhite['x'] + $M[0][1] * $sourceWhite['y'] + $M[0][2] * $sourceWhite['z'],
            $M[1][0] * $sourceWhite['x'] + $M[1][1] * $sourceWhite['y'] + $M[1][2] * $sourceWhite['z'],
            $M[2][0] * $sourceWhite['x'] + $M[2][1] * $sourceWhite['y'] + $M[2][2] * $sourceWhite['z']
        ];

        $targetWhiteRgb = [
            $M[0][0] * $targetWhite['x'] + $M[0][1] * $targetWhite['y'] + $M[0][2] * $targetWhite['z'],
            $M[1][0] * $targetWhite['x'] + $M[1][1] * $targetWhite['y'] + $M[1][2] * $targetWhite['z'],
            $M[2][0] * $targetWhite['x'] + $M[2][1] * $targetWhite['y'] + $M[2][2] * $targetWhite['z']
        ];

        // Calculate adaptation ratios
        $ratios = [
            $targetWhiteRgb[0] / $sourceWhiteRgb[0],
            $targetWhiteRgb[1] / $sourceWhiteRgb[1],
            $targetWhiteRgb[2] / $sourceWhiteRgb[2]
        ];

        // Apply adaptation
        $adaptedRgb = [
            $sourceRgb[0] * $ratios[0],
            $sourceRgb[1] * $ratios[1],
            $sourceRgb[2] * $ratios[2]
        ];

        // Convert back to XYZ using inverse Bradford matrix
        $MInv = [
            [0.9869929, -0.1470543, 0.1599627],
            [0.4323053, 0.5183603, 0.0492912],
            [-0.0085287, 0.0400428, 0.9684867]
        ];

        return [
            'x' => $MInv[0][0] * $adaptedRgb[0] + $MInv[0][1] * $adaptedRgb[1] + $MInv[0][2] * $adaptedRgb[2],
            'y' => $MInv[1][0] * $adaptedRgb[0] + $MInv[1][1] * $adaptedRgb[1] + $MInv[1][2] * $adaptedRgb[2],
            'z' => $MInv[2][0] * $adaptedRgb[0] + $MInv[2][1] * $adaptedRgb[1] + $MInv[2][2] * $adaptedRgb[2]
        ];
    }

    /**
     * Von Kries chromatic adaptation transform.
     * 
     * @param array{x: float, y: float, z: float} $xyz Source XYZ
     * @param array{x: float, y: float, z: float} $sourceWhite Source reference white
     * @param array{x: float, y: float, z: float} $targetWhite Target reference white
     * @return array{x: float, y: float, z: float} Adapted XYZ
     */
    private function vonKriesAdaptation(array $xyz, array $sourceWhite, array $targetWhite): array
    {
        // Von Kries uses simple scaling
        return $this->xyzScalingAdaptation($xyz, $sourceWhite, $targetWhite);
    }

    /**
     * XYZ Scaling chromatic adaptation.
     * 
     * @param array{x: float, y: float, z: float} $xyz Source XYZ
     * @param array{x: float, y: float, z: float} $sourceWhite Source reference white
     * @param array{x: float, y: float, z: float} $targetWhite Target reference white
     * @return array{x: float, y: float, z: float} Adapted XYZ
     */
    private function xyzScalingAdaptation(array $xyz, array $sourceWhite, array $targetWhite): array
    {
        return [
            'x' => $xyz['x'] * ($targetWhite['x'] / $sourceWhite['x']),
            'y' => $xyz['y'] * ($targetWhite['y'] / $sourceWhite['y']),
            'z' => $xyz['z'] * ($targetWhite['z'] / $sourceWhite['z'])
        ];
    }

    #[\Override]
    final public function toArray(): array
    {
        return [
            'color-space' => $this->getColorSpaceName(),
            'values' => $this->values,
            'illuminant' => $this->illuminant->value,
            'observer' => $this->observer->value,
        ];
    }

    #[\Override]
    final public function __toString(): string
    {
        // Always use clamped values for display
        $clampedValues = [];
        foreach ($this->getChannels() as $channel) {
            $clampedValues[] = $this->getChannel($channel);
        }
        return $this->getColorSpaceName() . '(' . implode(', ', $clampedValues) . ')';
    }

    abstract public function without(array $channels): static;
    abstract public function with(array $channels): static;

    /**
     * Serialize the color to JSON.
     * 
     * @return array<string, mixed>
     *
     * @see \JsonSerializable::jsonSerialize()
     */
    final public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Helper method to convert to a target color space using the registry system.
     * 
     * This method tries:
     * 1. Current color space's to{TargetSpace}() method (direct conversion)
     * 2. Target color space's from{CurrentSpace}() method (direct conversion)
     * 3. Conversion chain through RGB (A → RGB → B) (indirect conversion)
     * 
     * For indirect conversions, non-strict mode is used internally to preserve precision,
     * even if the current instance is in strict mode. The returned values are raw arrays
     * that should be used to create Color/MutableColor instances.
     * 
     * @param string $targetSpaceName The name of the target color space (e.g., "rgb", "hsl")
     * @param CIEIlluminant|null $illuminant Optional illuminant override
     * @param CIEObserver|null $observer Optional observer override
     * @return array{values: array<string, float>, strictMode: bool} Array with converted values and recommended strict mode
     * @throws ConversionNotSupportedException If conversion is not supported
     */
    protected function convertToColorSpace(
        string $targetSpaceName,
        ?CIEIlluminant $illuminant = null,
        ?CIEObserver $observer = null
    ): array {
        $currentSpaceName = $this->colorSpace::getName();
        
        // If already in target space, return current values
        if ($currentSpaceName === $targetSpaceName) {
            return ['values' => $this->values, 'strictMode' => $this->strictClamping];
        }

        // Try method 1: Current color space's to{TargetSpace}() method (direct)
        $conversionAttempts = [];
        try {
            $methodName = 'to' . ucfirst($targetSpaceName);
            if (method_exists($this->colorSpace, $methodName)) {
                // Determine if we need to pass CIE parameters
                $supportsCIE = $this->colorSpace::supportsIlluminant() || $this->colorSpace::supportsObserver();
                $illuminant = $illuminant ?? $this->illuminant;
                $observer = $observer ?? $this->observer;
                
                $values = $supportsCIE
                    ? $this->colorSpace::$methodName($this->values, $illuminant, $observer)
                    : $this->colorSpace::$methodName($this->values);
                
                // Direct conversion: use instance's strict mode
                return ['values' => $values, 'strictMode' => $this->strictClamping];
            }
        } catch (\BadMethodCallException $e) {
            // Method doesn't exist, try fallback
            $conversionAttempts[] = "Direct method '{$methodName}' not found";
        } catch (\Exception $e) {
            // Log conversion failure but continue trying other methods
            $conversionAttempts[] = "Direct conversion failed: " . $e->getMessage();
            error_log(
                sprintf(
                    "[Negarity Color] Conversion attempt failed: %s -> %s via direct method. Error: %s",
                    $currentSpaceName,
                    $targetSpaceName,
                    $e->getMessage()
                )
            );
        }

        // Try method 2: Target color space's from{CurrentSpace}() method (direct)
        try {
            if (!ColorSpaceRegistry::has($targetSpaceName)) {
                throw new ColorSpaceNotFoundException("Color space '{$targetSpaceName}' not registered.");
            }
            
            $targetSpaceClass = ColorSpaceRegistry::get($targetSpaceName);
            $methodName = 'from' . ucfirst($currentSpaceName);
            
            if (method_exists($targetSpaceClass, $methodName)) {
                // Determine if we need to pass CIE parameters
                $supportsCIE = $this->colorSpace::supportsIlluminant() || $this->colorSpace::supportsObserver();
                $illuminant = $illuminant ?? $this->illuminant;
                $observer = $observer ?? $this->observer;
                
                $values = $supportsCIE
                    ? $targetSpaceClass::$methodName($this->values, $illuminant, $observer)
                    : $targetSpaceClass::$methodName($this->values);
                
                // Direct conversion: use instance's strict mode
                return ['values' => $values, 'strictMode' => $this->strictClamping];
            }
        } catch (ColorSpaceNotFoundException $e) {
            // Re-throw color space not found exceptions immediately
            throw $e;
        } catch (\BadMethodCallException $e) {
            // Method doesn't exist, try fallback
            $conversionAttempts[] = "Reverse method '{$methodName}' not found";
        } catch (\Exception $e) {
            // Log conversion failure but continue trying other methods
            $conversionAttempts[] = "Reverse conversion failed: " . $e->getMessage();
            error_log(
                sprintf(
                    "[Negarity Color] Conversion attempt failed: %s -> %s via reverse method. Error: %s",
                    $currentSpaceName,
                    $targetSpaceName,
                    $e->getMessage()
                )
            );
        }

        // Try method 3: Conversion chain through RGB (A → RGB → B) (indirect)
        // This allows any color space to convert to any other via RGB as intermediate
        // Note: We skip this if either source or target is RGB to avoid circular conversion
        // For indirect conversions, we use non-strict mode to preserve precision
        if ($targetSpaceName !== 'rgb' && $currentSpaceName !== 'rgb') {
            try {
                // First convert current space to RGB (this should always work since all spaces have toRGB/fromRGB)
                // We use a direct call to avoid recursion - check if current space has toRGB or RGB has from{CurrentSpace}
                $rgbValues = null;
                
                // Try current space's toRGB method
                if (method_exists($this->colorSpace, 'toRGB')) {
                    $supportsCIE = $this->colorSpace::supportsIlluminant() || $this->colorSpace::supportsObserver();
                    $illuminantForRgb = $illuminant ?? $this->illuminant;
                    $observerForRgb = $observer ?? $this->observer;
                    
                    if ($supportsCIE) {
                        $rgbValues = $this->colorSpace::toRGB($this->values, $illuminantForRgb, $observerForRgb);
                    } else {
                        $rgbValues = $this->colorSpace::toRGB($this->values);
                    }
                } 
                // Try RGB's from{CurrentSpace} method
                elseif (ColorSpaceRegistry::has('rgb')) {
                    $rgbClass = ColorSpaceRegistry::get('rgb');
                    $fromMethod = 'from' . ucfirst($currentSpaceName);
                    
                    if (method_exists($rgbClass, $fromMethod)) {
                        $supportsCIE = $this->colorSpace::supportsIlluminant() || $this->colorSpace::supportsObserver();
                        $illuminantForRgb = $illuminant ?? $this->illuminant;
                        $observerForRgb = $observer ?? $this->observer;
                        
                        if ($supportsCIE) {
                            $rgbValues = $rgbClass::$fromMethod($this->values, $illuminantForRgb, $observerForRgb);
                        } else {
                            $rgbValues = $rgbClass::$fromMethod($this->values);
                        }
                    }
                }
                
                if ($rgbValues === null) {
                    throw new ConversionNotSupportedException("Cannot convert '{$currentSpaceName}' to RGB for intermediate conversion.");
                }
                
                // Then convert RGB to target space
                if (!ColorSpaceRegistry::has($targetSpaceName)) {
                    throw new ColorSpaceNotFoundException("Color space '{$targetSpaceName}' not registered.");
                }
                
                $targetSpaceClass = ColorSpaceRegistry::get($targetSpaceName);
                $fromRgbMethod = 'fromRGB';
                
                if (method_exists($targetSpaceClass, $fromRgbMethod)) {
                    // Check if target space supports CIE parameters
                    $targetSupportsCIE = $targetSpaceClass::supportsIlluminant() || $targetSpaceClass::supportsObserver();
                    $illuminant = $illuminant ?? $this->illuminant;
                    $observer = $observer ?? $this->observer;
                    
                    // Determine alpha value (for RGBA/HSLA)
                    $alphaValue = 255;
                    if ($targetSpaceName === 'rgba' || $targetSpaceName === 'hsla') {
                        // If source has alpha, preserve it; otherwise use default 255
                        if ($currentSpaceName === 'rgba' && isset($this->values['a'])) {
                            $alphaValue = $this->values['a'];
                        } elseif ($currentSpaceName === 'hsla' && isset($this->values['a'])) {
                            $alphaValue = $this->values['a'];
                        }
                    }
                    
                    // For color spaces with CIE support, pass CIE parameters
                    if ($targetSupportsCIE) {
                        $illuminant = $illuminant ?? $this->illuminant;
                        $observer = $observer ?? $this->observer;
                        return $targetSpaceClass::$fromRgbMethod($rgbValues, $alphaValue, $illuminant, $observer);
                    } else {
                        return $targetSpaceClass::$fromRgbMethod($rgbValues, $alphaValue);
                    }
                }
            } catch (ColorSpaceNotFoundException $e) {
                // Re-throw color space not found exceptions immediately
                throw $e;
            } catch (\Exception $e) {
                // Log RGB conversion chain failure
                $conversionAttempts[] = "RGB conversion chain failed: " . $e->getMessage();
                error_log(
                    sprintf(
                        "[Negarity Color] Conversion attempt failed: %s -> %s via RGB chain. Error: %s",
                        $currentSpaceName,
                        $targetSpaceName,
                        $e->getMessage()
                    )
                );
            }
        }

        // If we get here, no conversion method worked
        // Build detailed error message with conversion attempts
        $errorMessage = sprintf(
            "Conversion from '%s' to '%s' is not supported. " .
            "Neither direct conversion methods nor RGB conversion chain are available.",
            $currentSpaceName,
            $targetSpaceName
        );
        
        if (!empty($conversionAttempts)) {
            $errorMessage .= " Attempted methods: " . implode('; ', $conversionAttempts);
        }

        throw new ConversionNotSupportedException($errorMessage);
    }

    /**
     * Create a Color from a hex string.
     * 
     * @param string $value Hex string (e.g. "#RRGGBB" or "RRGGBBAA")
     * @param class-string<ColorSpaceInterface>|string $colorSpace Color space class or name (default: "rgb")
     * @return static
     * @throws \InvalidArgumentException
     */
    final public static function hex(string $value, string $colorSpace = 'rgb'): static
    {
        $value = ltrim($value, '#');
        $r = $g = $b = $a = 0;

        if (strlen($value) === 8) {
            $r = hexdec(substr($value, 0, 2));
            $g = hexdec(substr($value, 2, 2));
            $b = hexdec(substr($value, 4, 2));
            $a = hexdec(substr($value, 6, 2));
        } else if (strlen($value) === 6) {
            $r = hexdec(substr($value, 0, 2));
            $g = hexdec(substr($value, 2, 2));
            $b = hexdec(substr($value, 4, 2));
            $a = 255;
        } else if (strlen($value) === 4) {
            $r = hexdec(str_repeat(substr($value, 0, 1), 2));
            $g = hexdec(str_repeat(substr($value, 1, 1), 2));
            $b = hexdec(str_repeat(substr($value, 2, 1), 2));
            $a = hexdec(str_repeat(substr($value, 3, 1), 2));
        } else if (strlen($value) === 3) {
            $r = hexdec(str_repeat(substr($value, 0, 1), 2));
            $g = hexdec(str_repeat(substr($value, 1, 1), 2));
            $b = hexdec(str_repeat(substr($value, 2, 1), 2));
            $a = 255;
        } else {
            throw new InvalidFormatException(
                sprintf(
                    'Hex value must be 3 (rgb), 4 (rgba), 6 (rrggbb), or 8 (rrggbbaa) characters long. Got %d characters.',
                    strlen($value)
                )
            );
        }

        // First, create RGB color using the registry via __callStatic
        if (!ColorSpaceRegistry::has('rgb')) {
            throw new ColorSpaceNotFoundException('RGB color space must be registered to use hex() method. Call ColorSpaceRegistry::registerBuiltIn() first.');
        }
        
        // Use __callStatic to create RGB color
        $rgbColor = static::rgb($r, $g, $b);
        
        // If target color space is RGB, return it
        $targetSpaceName = is_a($colorSpace, ColorSpaceInterface::class, true) 
            ? $colorSpace::getName() 
            : strtolower($colorSpace);
            
        if ($targetSpaceName === 'rgb') {
            return $rgbColor;
        }
        
        // Convert to target color space
        if (!ColorSpaceRegistry::has($targetSpaceName)) {
            throw new ColorSpaceNotFoundException("Color space '{$targetSpaceName}' not registered.");
        }
        
        // Handle special cases for alpha channels
        if ($targetSpaceName === 'rgba') {
            return $rgbColor->toRGBA($a);
        }
        
        if ($targetSpaceName === 'hsla') {
            return $rgbColor->toHSLA($a);
        }
        
        // For other color spaces, use the conversion method
        $methodName = 'to' . ucfirst($targetSpaceName);
        if (method_exists($rgbColor, $methodName)) {
            return $rgbColor->$methodName();
        }
        
        throw new \InvalidArgumentException("Conversion to '{$targetSpaceName}' is not supported from hex input.");
    }

    /**
     * Create a Color from a named color or color space factory method.
     * 
     * Precedence: Named colors first, then color spaces.
     * Conflict detection: If both a named color and color space exist with the same name,
     * a warning is logged and the named color takes precedence.
     * 
     * @param string $name Named color (e.g. "red", "blue") or color space name (e.g. "rgb", "hsl")
     * @param array $arguments Arguments for the factory method
     * @return static
     * @throws \InvalidArgumentException
     */
    public static function __callStatic(string $name, array $arguments): static
    {
        $colorName = strtolower($name);
        
        // Check for conflicts: both named color and color space exist
        $hasNamedColor = false;
        $hasColorSpace = ColorSpaceRegistry::has($colorName);
        
        $colorSpace = $arguments[0] ?? RGB::class;
        if (is_string($colorSpace) && class_exists($colorSpace)) {
            foreach (static::$registries as $registry) {
                if ($registry->has($colorName, $colorSpace)) {
                    $hasNamedColor = true;
                    break;
                }
            }
        }
        
        // Conflict detection: warn if both exist
        if ($hasNamedColor && $hasColorSpace) {
            // Log a warning (in production, you might want to use a proper logger)
            error_log(
                "Warning: Both named color '{$colorName}' and color space '{$colorName}' exist. " .
                "Named color takes precedence."
            );
        }
        
        // First, check named color registries (named colors take precedence)
        if (is_string($colorSpace) && class_exists($colorSpace)) {
            foreach (static::$registries as $registry) {
                if ($registry->has($colorName, $colorSpace)) {
                    try {
                        // registry gives back an array of channel values
                        $values = $registry->getColorValuesByName($colorName, $colorSpace);
                        // you just wrap that into a Color object
                        return new static($colorSpace, $values);
                    } catch (\Exception $e) {
                        // Log registry error but continue to try color space registry
                        error_log(
                            sprintf(
                                "[Negarity Color] Named color registry error for '{$colorName}' in '{$colorSpace}': %s",
                                $e->getMessage()
                            )
                        );
                    }
                }
            }
        }

        // Then, check color space registry
        if ($hasColorSpace) {
            try {
                $colorSpaceClass = ColorSpaceRegistry::get($colorName);
                $channels = $colorSpaceClass::getChannels();
                $supportsAlpha = $colorSpaceClass::supportAlphaChannel();
                $alphaChannelName = $supportsAlpha ? $colorSpaceClass::getAlphaChannelName() : '';
                
                // Check if first argument is an array (named arguments-like)
                $values = [];
                $argIndex = 0;
                
                if (!empty($arguments) && is_array($arguments[0]) && !isset($arguments[1])) {
                    // Named arguments-like: Color::rgb(['r' => 255, 'g' => 100, 'b' => 50])
                    $arrayInput = $arguments[0];
                    
                    // Validate all keys exist as channels
                    foreach (array_keys($arrayInput) as $key) {
                        if (!in_array($key, $channels, true)) {
                            throw new InvalidColorValueException(
                                "Invalid channel '{$key}' for color space '{$colorName}'. " .
                                "Valid channels are: " . implode(', ', $channels)
                            );
                        }
                    }
                    
                    // Build values from array, using defaults for missing channels
                    foreach ($channels as $channel) {
                        if (isset($arrayInput[$channel])) {
                            $values[$channel] = $arrayInput[$channel];
                        } else {
                            // For alpha channel, default to 255 if not provided
                            if ($channel === $alphaChannelName) {
                                $values[$channel] = 255.0;
                            } else {
                                $values[$channel] = $colorSpaceClass::getChannelDefaultValue($channel);
                            }
                        }
                    }
                    
                    // Check for CIE parameters in remaining arguments (after array)
                    $illuminant = null;
                    $observer = null;
                    if ($colorSpaceClass::supportsIlluminant() || $colorSpaceClass::supportsObserver()) {
                        if (isset($arguments[1]) && $arguments[1] instanceof CIEIlluminant) {
                            $illuminant = $arguments[1];
                        }
                        if (isset($arguments[2]) && $arguments[2] instanceof CIEObserver) {
                            $observer = $arguments[2];
                        }
                    }
                    
                    return new static($colorSpaceClass, $values, $illuminant, $observer);
                }
                
                // Positional arguments: Color::rgb(255, 100, 50)
                // Separate color channels from alpha and CIE parameters
                $colorChannels = $channels;
                if ($supportsAlpha && $alphaChannelName !== '') {
                    // Remove alpha from regular channels - it will be handled separately
                    $colorChannels = array_filter($channels, fn($ch) => $ch !== $alphaChannelName);
                }
                
                // Build values array from positional arguments
                foreach ($colorChannels as $channel) {
                    if (isset($arguments[$argIndex])) {
                        $values[$channel] = $arguments[$argIndex];
                        $argIndex++;
                    } else {
                        $values[$channel] = $colorSpaceClass::getChannelDefaultValue($channel);
                    }
                }
                
                // Handle alpha channel separately (optional, defaults to 255)
                if ($supportsAlpha && $alphaChannelName !== '') {
                    if (isset($arguments[$argIndex]) && is_int($arguments[$argIndex])) {
                        // Explicit alpha provided (accept any int, clamping happens later)
                        $values[$alphaChannelName] = (float)$arguments[$argIndex];
                        $argIndex++;
                    } else {
                        // Default alpha to 255 (fully opaque)
                        $values[$alphaChannelName] = 255.0;
                    }
                }
                
                // Validate argument count (excluding CIE parameters)
                $expectedArgs = count($colorChannels) + ($supportsAlpha ? 1 : 0);
                $actualArgs = $argIndex;
                $cieArgsCount = 0;
                
                // Handle CIE parameters for color spaces that support them
                $illuminant = null;
                $observer = null;
                if ($colorSpaceClass::supportsIlluminant() || $colorSpaceClass::supportsObserver()) {
                    // Check if illuminant/observer are provided after channel arguments
                    if (isset($arguments[$argIndex]) && $arguments[$argIndex] instanceof CIEIlluminant) {
                        $illuminant = $arguments[$argIndex];
                        $argIndex++;
                        $cieArgsCount++;
                    }
                    if (isset($arguments[$argIndex]) && $arguments[$argIndex] instanceof CIEObserver) {
                        $observer = $arguments[$argIndex];
                        $argIndex++;
                        $cieArgsCount++;
                    }
                }
                
                // Check for too many arguments (excluding CIE parameters)
                $totalProvidedArgs = count($arguments);
                $maxExpectedArgs = $expectedArgs + $cieArgsCount;
                
                if ($totalProvidedArgs > $maxExpectedArgs) {
                    $extraArgs = $totalProvidedArgs - $maxExpectedArgs;
                    $cieInfo = $cieArgsCount > 0 ? " plus {$cieArgsCount} optional CIE parameter(s)" : "";
                    throw new InvalidColorValueException(
                        sprintf(
                            "Too many arguments provided for color space '%s'. " .
                            "Expected %d argument(s)%s, got %d. " .
                            "Extra %d argument(s) will be ignored.",
                            $colorName,
                            $expectedArgs,
                            $cieInfo,
                            $totalProvidedArgs,
                            $extraArgs
                        )
                    );
                }
                
                    return new static($colorSpaceClass, $values, $illuminant, $observer);
            } catch (ColorSpaceNotFoundException $e) {
                // Re-throw color space not found exceptions
                throw $e;
            } catch (InvalidColorValueException $e) {
                // Re-throw invalid color value exceptions as-is
                throw $e;
            } catch (\Exception $e) {
                // Log unexpected errors but re-throw as ColorSpaceNotFoundException
                error_log(
                    sprintf(
                        "[Negarity Color] Unexpected error accessing color space '{$colorName}': %s",
                        $e->getMessage()
                    )
                );
                throw new ColorSpaceNotFoundException(
                    "Color space '{$colorName}' not found or cannot be accessed: " . $e->getMessage(),
                    0,
                    $e
                );
            }
        }

        throw new ColorSpaceNotFoundException(
            "Named color or color space '{$colorName}' not found. " .
            "Make sure the color space is registered using ColorSpaceRegistry::registerBuiltIn() or ColorSpaceRegistry::register()."
        );
    }

    /**
     * Dynamically handle filter method calls.
     * 
     * @param string $name
     * @param array<int, mixed> $arguments
     * @return mixed
     * @throws \BadMethodCallException
     * @see FilterRegistry
     */
    public function __call(string $name, array $arguments): mixed
    {
        $reservedMrthods = [ 'ColorSpace', 'ColorSpaceName', 'Channels', 'Channel' ];
        // Handle get{ChannelName} and get{ChannelName}Raw calls
        if (str_starts_with($name, 'get') && !in_array(substr($name, 3), $reservedMrthods, true)) {
            $suffix = 'Raw';
            $isRaw = str_ends_with($name, $suffix) && strlen($name) > strlen($suffix);
            
            if ($isRaw) {
                $channelName = lcfirst(substr($name, 3, -strlen($suffix)));
            } else {
                $channelName = lcfirst(substr($name, 3));
            }
            
            if (in_array($channelName, $this->getChannels(), true)) {
                if ($isRaw) {
                    return $this->getChannelRaw($channelName);
                }
                // For regular getters, use appropriate value based on mode for operations
                // But for public API, always return clamped
                return $this->getChannel($channelName);
            } else {
                throw new InvalidColorValueException("Channel '{$channelName}' does not exist in color space '{$this->getColorSpaceName()}'.");
            }
        }

        if (!FilterRegistry::has($name)) {
            throw new FilterNotFoundException("Filter '{$name}' not found.");
        }
    
        try {
            $filter = FilterRegistry::get($name);
        } catch (FilterNotFoundException $e) {
            // Re-throw filter not found exceptions
            throw $e;
        } catch (\Exception $e) {
            // Log unexpected errors but re-throw as FilterNotFoundException
            error_log(
                sprintf(
                    "[Negarity Color] Unexpected error accessing filter '{$name}': %s",
                    $e->getMessage()
                )
            );
            throw new FilterNotFoundException(
                "Filter '{$name}' not found or cannot be accessed: " . $e->getMessage(),
                0,
                $e
            );
        }
    
        // Unary
        if ($filter instanceof UnaryColorFilterInterface) {
            return $filter->apply($this);
        }
    
        // Parameterized
        if ($filter instanceof ParameterizedColorFilterInterface) {
            $value = $arguments[0] ?? null;
            return $filter->apply($this, $value);
        }
    
        // Binary: $color->blend($otherColor)
        if ($filter instanceof BinaryColorFilterInterface) {
            $other = $arguments[0] ?? null;
            return $filter->apply($this, $other);
        }
    
        throw new \BadMethodCallException("Method {$name} does not exist.");
    }



    /**
     * Convert the color to a hex string.
     * 
     * @return string
     * @throws \InvalidArgumentException
     */
    final public function toHex(): string
    {
        if ($this->colorSpace === RGB::class) {
            return sprintf(
                '#%02X%02X%02X',
                $this->getChannel('r'),
                $this->getChannel('g'),
                $this->getChannel('b')
            );
        } else if ($this->colorSpace === RGBA::class) {
            return sprintf(
                '#%02X%02X%02X%02X',
                $this->getChannel('r'),
                $this->getChannel('g'),
                $this->getChannel('b'),
                $this->getChannel('a')
            );
        } else {
            return $this->toRGB()->toHex();
        }
    }
}
