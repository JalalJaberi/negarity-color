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
use Negarity\Color\CIE\CIEIlluminant;
use Negarity\Color\CIE\CIEObserver;

final class Color extends AbstractColor
{
    /**
     * Create a new Color instance.
     * 
     * @param class-string<ColorSpaceInterface> $colorSpace
     * @param array<string, float|int> $values
     * @param CIEIlluminant|null $illuminant CIE standard illuminant (default: D65)
     * @param CIEObserver|null $observer CIE standard observer (default: TwoDegree)
     * @return void
     * @throws \InvalidArgumentException
     */
    public function __construct(
        string $colorSpace, 
        array $values = [],
        ?CIEIlluminant $illuminant = null,
        ?CIEObserver $observer = null,
        ?bool $strictClamping = null
    ) {
        parent::__construct($colorSpace, $values, $illuminant, $observer, $strictClamping);
    }

    #[\Override]
    public function without(array $channels): static
    {
        $values = $this->values;
        foreach ($channels as $channel) {
            if (!in_array($channel, $this->getChannels(), true)) {
                throw new \InvalidArgumentException("Channel '{$channel}' does not exist in color space '{$this->getColorSpaceName()}'.");
            }
            $values[$channel] = $this->colorSpace::getChannelDefaultValue($channel);
        }

        return new self($this->colorSpace, $values, $this->illuminant, $this->observer, $this->strictClamping);
    }

    #[\Override]
    public function with(array $channels): static
    {
        $values = $this->values;
        
        foreach ($channels as $channel => $value) {
            if (!in_array($channel, $this->getChannels(), true)) {
                throw new \InvalidArgumentException("Channel '{$channel}' does not exist in color space '{$this->getColorSpaceName()}'.");
            }
            $type = gettype($value);
            if ($type !== 'integer' && $type !== 'double' && $type !== 'float') {
                throw new \InvalidArgumentException("Channel '{$channel}' must be of type int or float.");
            }
            // Convert to float and always store original value (never clamp in storage)
            // Type validation already done above (int/float check)
            // Range validation: In strict mode we clamp on-the-fly, in non-strict we allow out-of-range
            $floatValue = (float)$value;
            $values[$channel] = $floatValue;
        }

        // Create new instance with same strict clamping mode
        return new self($this->colorSpace, $values, $this->illuminant, $this->observer, $this->strictClamping);
    }

    #[\Override]
    public function toRGB(): static
    {
        $rgbValues = $this->convertToColorSpace('rgb');
        return new self(RGB::class, $rgbValues, $this->illuminant, $this->observer, $this->strictClamping);
    }

    #[\Override]
    public function toRGBA(int $alpha = 255): static
    {
        if ($alpha < 0 || $alpha > 255) {
            throw new \InvalidArgumentException('Alpha value must be between 0 and 255');
        }

        // If already RGBA, return self (or update alpha if different)
        if ($this->colorSpace === RGBA::class) {
            if ($this->getA() === $alpha) {
                return new self($this->colorSpace, $this->values, $this->illuminant, $this->observer, $this->strictClamping);
            }
            // Update alpha
            $values = $this->values;
            $values['a'] = $alpha;
            return new self($this->colorSpace, $values, $this->illuminant, $this->observer, $this->strictClamping);
        }

        // If HSLA, preserve alpha
        if ($this->colorSpace === HSLA::class) {
            $alpha = $this->getA();
        }

        // Convert to RGB first, then to RGBA
        $rgb = $this->toRGB();
        $rgbaValues = RGBA::fromRGB($rgb->values, $alpha);
        $rgbaClass = ColorSpaceRegistry::get('rgba');
        return new self($rgbaClass, $rgbaValues, $this->illuminant, $this->observer, $this->strictClamping);
    }

    #[\Override]
    public function toCMYK(): static
    {
        $cmykValues = $this->convertToColorSpace('cmyk');
        $cmykClass = ColorSpaceRegistry::get('cmyk');
        return new self($cmykClass, $cmykValues, $this->illuminant, $this->observer, $this->strictClamping);
    }

    #[\Override]
    public function toHSL(): static
    {
        $hslValues = $this->convertToColorSpace('hsl');
        $hslClass = ColorSpaceRegistry::get('hsl');
        return new self($hslClass, $hslValues, $this->illuminant, $this->observer, $this->strictClamping);
    }

    #[\Override]
    public function toHSLA(int $alpha = 255): static
    {
        if ($alpha < 0 || $alpha > 255) {
            throw new \InvalidArgumentException('Alpha value must be between 0 and 255');
        }

        // If already HSLA, return self (or update alpha if different)
        if ($this->colorSpace === HSLA::class) {
            if ($this->getA() === $alpha) {
                return new self($this->colorSpace, $this->values, $this->illuminant, $this->observer, $this->strictClamping);
            }
            // Update alpha
            $values = $this->values;
            $values['a'] = $alpha;
            return new self($this->colorSpace, $values, $this->illuminant, $this->observer, $this->strictClamping);
        }

        // If HSL, convert to HSLA with specified alpha
        if ($this->colorSpace === HSL::class) {
            $values = $this->values;
            $values['a'] = $alpha;
            $hslaClass = ColorSpaceRegistry::get('hsla');
            return new self($hslaClass, $values, $this->illuminant, $this->observer, $this->strictClamping);
        }

        // If RGBA, preserve alpha
        if ($this->colorSpace === RGBA::class) {
            $alpha = $this->getA();
        }

        // Convert to HSL first, then to HSLA
        $hsl = $this->toHSL();
        $hslaValues = HSLA::fromRGB($hsl->toRGB()->values, $alpha);
        $hslaClass = ColorSpaceRegistry::get('hsla');
        return new self($hslaClass, $hslaValues, $this->illuminant, $this->observer, $this->strictClamping);
    }

    #[\Override]
    public function toHSV(): static
    {
        $hsvValues = $this->convertToColorSpace('hsv');
        $hsvClass = ColorSpaceRegistry::get('hsv');
        return new self($hsvClass, $hsvValues, $this->illuminant, $this->observer, $this->strictClamping);
    }

    #[\Override]
    public function toLab(?CIEIlluminant $illuminant = null, ?CIEObserver $observer = null): static
    {
        $illuminant = $illuminant ?? $this->illuminant;
        $observer = $observer ?? $this->observer;
        $labValues = $this->convertToColorSpace('lab', $illuminant, $observer);
        $labClass = ColorSpaceRegistry::get('lab');
        return new self($labClass, $labValues, $illuminant, $observer, $this->strictClamping);
    }

    #[\Override]
    public function toLCh(?CIEIlluminant $illuminant = null, ?CIEObserver $observer = null): static
    {
        $illuminant = $illuminant ?? $this->illuminant;
        $observer = $observer ?? $this->observer;
        $lchValues = $this->convertToColorSpace('lch', $illuminant, $observer);
        $lchClass = ColorSpaceRegistry::get('lch');
        return new self($lchClass, $lchValues, $illuminant, $observer, $this->strictClamping);
    }

    #[\Override]
    public function toXYZ(?CIEIlluminant $illuminant = null, ?CIEObserver $observer = null): static
    {
        $illuminant = $illuminant ?? $this->illuminant;
        $observer = $observer ?? $this->observer;
        $xyzValues = $this->convertToColorSpace('xyz', $illuminant, $observer);
        $xyzClass = ColorSpaceRegistry::get('xyz');
        return new self($xyzClass, $xyzValues, $illuminant, $observer, $this->strictClamping);
    }

    #[\Override]
    public function toYCbCr(): static
    {
        $ycbcrValues = $this->convertToColorSpace('ycbcr');
        $ycbcrClass = ColorSpaceRegistry::get('ycbcr');
        return new self($ycbcrClass, $ycbcrValues, $this->illuminant, $this->observer, $this->strictClamping);
    }

    #[\Override]
    public function withIlluminant(CIEIlluminant $illuminant): static
    {
        if (!$this->colorSpace::supportsIlluminant()) {
            throw new \RuntimeException(
                "Color space '{$this->getColorSpaceName()}' does not support illuminants."
            );
        }
        return new self($this->colorSpace, $this->values, $illuminant, $this->observer, $this->strictClamping);
    }

    #[\Override]
    public function adaptIlluminant(
        CIEIlluminant $targetIlluminant,
        ?\Negarity\Color\CIE\AdaptationMethod $method = null
    ): static {
        if (!$this->colorSpace::supportsIlluminant()) {
            throw new \RuntimeException(
                "Color space '{$this->getColorSpaceName()}' does not support illuminants."
            );
        }

        $method = $method ?? \Negarity\Color\CIE\AdaptationMethod::Bradford;

        // Convert to XYZ with current illuminant
        $xyz = $this->toXYZ();
        $xyzValues = ['x' => $xyz->getX(), 'y' => $xyz->getY(), 'z' => $xyz->getZ()];

        // Perform chromatic adaptation
        $adaptedXyz = $this->performChromaticAdaptation(
            $xyzValues,
            $this->illuminant,
            $targetIlluminant,
            $this->observer,
            $method
        );

        // Convert adapted XYZ back to original color space with new illuminant
        $originalSpaceClass = $this->colorSpace;
        $originalSpaceName = $this->getColorSpaceName();
        
        // Convert XYZ to RGB first, then to original space
        $rgbValues = \Negarity\Color\ColorSpace\XYZ::toRGB($adaptedXyz, $targetIlluminant, $this->observer);
        $adaptedValues = $originalSpaceClass::fromRGB($rgbValues, 255, $targetIlluminant, $this->observer);

        return new self($originalSpaceClass, $adaptedValues, $targetIlluminant, $this->observer, $this->strictClamping);
    }

    #[\Override]
    public function adaptObserver(CIEObserver $targetObserver): static
    {
        if (!$this->colorSpace::supportsObserver()) {
            throw new \RuntimeException(
                "Color space '{$this->getColorSpaceName()}' does not support observers."
            );
        }

        // Convert to RGB (observer-independent intermediate space)
        $rgb = $this->toRGB();
        $rgbValues = ['r' => $rgb->getR(), 'g' => $rgb->getG(), 'b' => $rgb->getB()];

        // Convert RGB back to original color space with new observer
        // This will use the new observer's reference white for Lab/XYZ conversions
        $originalSpaceClass = $this->colorSpace;
        $adaptedValues = $originalSpaceClass::fromRGB($rgbValues, 255, $this->illuminant, $targetObserver);

        return new self($originalSpaceClass, $adaptedValues, $this->illuminant, $targetObserver, $this->strictClamping);
    }
}
