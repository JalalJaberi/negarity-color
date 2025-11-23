<?php

declare(strict_types=1);

namespace Negarity\Color\ColorSpace;

use Negarity\Color\Exception\InvalidColorValueException;

final class LCh extends AbstractColorSpace
{
    public function __construct(
        private readonly int $l, // Lightness 0–100
        private readonly int $c, // Chroma 0–100 (approx, depends on gamut)
        private readonly int $h  // Hue angle 0–360
    ) {
        $this->assertRange($l, 0, 100, 'l');
        $this->assertRange($c, 0, 100, 'c');
        $this->assertRange($h, 0, 360, 'h');
    }

    public function getName(): string
    {
        return 'lch';
    }

    public function getChannels(): array
    {
        return ['l', 'c', 'h'];
    }

    public function getChannel(string $name): int
    {
        return match ($name) {
            'l' => $this->l,
            'c' => $this->c,
            'h' => $this->h,
            default => throw new InvalidColorValueException("Unknown channel: $name"),
        };
    }

    public function toArray(): array
    {
        return ['l' => $this->l, 'c' => $this->c, 'h' => $this->h];
    }

    public function without(array $channels): static
    {
        return new self(
            $channels['l'] ?? 0,
            $channels['c'] ?? 0,
            $channels['h'] ?? 0
        );
    }

    public function with(array $channels): static
    {
        return new self(
            $channels['l'] ?? $this->l,
            $channels['c'] ?? $this->c,
            $channels['h'] ?? $this->h
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
