<?php

declare(strict_types=1);

namespace Negarity\Color\ColorSpace;

use Negarity\Color\Exception\InvalidColorValueException;
use Negarity\Color\ColorSpace\ColorSpaceEnum;

final class Lab extends AbstractColorSpace
{
    public function __construct(
        private readonly int $l,  // Lightness 0–100
        private readonly int $a,  // Green–Red -128–127
        private readonly int $b   // Blue–Yellow -128–127
    ) {
        $this->assertRange($l, 0, 100, 'l');
        $this->assertRange($a, -128, 127, 'a');
        $this->assertRange($b, -128, 127, 'b');
    }

    public function getName(): string
    {
        return ColorSpaceEnum::LAB->value;
    }

    public function getChannels(): array
    {
        return ['l', 'a', 'b'];
    }

    public function getChannel(string $name): int
    {
        return match ($name) {
            'l' => $this->l,
            'a' => $this->a,
            'b' => $this->b,
            default => throw new InvalidColorValueException("Unknown channel: $name"),
        };
    }

    public function toArray(): array
    {
        return ['l' => $this->l, 'a' => $this->a, 'b' => $this->b];
    }

    public function without(array $channels): static
    {
        return new self(
            in_array('l', $channels, true) ? 0 : $this->l,
            in_array('a', $channels, true) ? 0 : $this->a,
            in_array('b', $channels, true) ? 0 : $this->b
        );
    }

    public function with(array $channels): static
    {
        return new self(
            $channels['l'] ?? $this->l,
            $channels['a'] ?? $this->a,
            $channels['b'] ?? $this->b
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
