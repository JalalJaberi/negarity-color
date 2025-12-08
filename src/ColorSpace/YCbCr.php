<?php

declare(strict_types=1);

namespace Negarity\Color\ColorSpace;

use Negarity\Color\Exception\InvalidColorValueException;
use Negarity\Color\ColorSpace\ColorSpaceEnum;

final class YCbCr extends AbstractColorSpace
{
    public function __construct(
        private readonly int $y,  // Luminance 0–255
        private readonly int $cb, // Blue-difference Chroma 0–255
        private readonly int $cr  // Red-difference Chroma 0–255
    ) {
        $this->assertRange($y, 0, 255, 'y');
        $this->assertRange($cb, 0, 255, 'cb');
        $this->assertRange($cr, 0, 255, 'cr');
    }

    public function getName(): string
    {
        return ColorSpaceEnum::YCBCR->value;
    }

    public function getChannels(): array
    {
        return ['y', 'cb', 'cr'];
    }

    public function getChannel(string $name): int
    {
        return match ($name) {
            'y' => $this->y,
            'cb' => $this->cb,
            'cr' => $this->cr,
            default => throw new InvalidColorValueException("Unknown channel: $name"),
        };
    }

    public function toArray(): array
    {
        return ['y' => $this->y, 'cb' => $this->cb, 'cr' => $this->cr];
    }

    public function without(array $channels): static
    {
        return new self(
            in_array('y', $channels, true) ? 0 : $this->y,
            in_array('cb', $channels, true) ? 0 : $this->cb,
            in_array('cr', $channels, true) ? 0 : $this->cr
        );
    }

    public function with(array $channels): static
    {
        return new self(
            $channels['y'] ?? $this->y,
            $channels['cb'] ?? $this->cb,
            $channels['cr'] ?? $this->cr
        );
    }

    private function assertRange(int $value, int $min, int $max, string $channel): void
    {
        if ($value < $min || $value > $max) {
            throw new InvalidColorValueException(
                sprintf('Channel "%s" must be between %d and %d, got %d', $channel, $min, $max, $value)
            );
        }
    }
}
