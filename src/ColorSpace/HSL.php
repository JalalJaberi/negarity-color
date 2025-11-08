<?php

declare(strict_types=1);

namespace Negarity\Color\ColorSpace;

use Negarity\Color\Exception\InvalidColorValueException;

final class HSL implements ColorSpaceInterface
{
    public function __construct(
        private readonly int $h, // 0–360
        private readonly int $s, // 0–100
        private readonly int $l  // 0–100
    ) {
        $this->assertRange($h, 0, 360, 'h');
        $this->assertRange($s, 0, 100, 's');
        $this->assertRange($l, 0, 100, 'l');
    }

    public function getName(): string
    {
        return 'hsl';
    }

    public function getChannels(): array
    {
        return ['h', 's', 'l'];
    }

    public function getChannel(string $name): int
    {
        return match ($name) {
            'h' => $this->h,
            's' => $this->s,
            'l' => $this->l,
            default => throw new InvalidColorValueException("Unknown channel: $name"),
        };
    }

    public function toArray(): array
    {
        return ['h' => $this->h, 's' => $this->s, 'l' => $this->l];
    }

    public function without(array $channels): static
    {
        return new self(
            in_array('h', $channels, true) ? 0 : $this->h,
            in_array('s', $channels, true) ? 0 : $this->s,
            in_array('l', $channels, true) ? 0 : $this->l
        );
    }

    public function with(array $channels): static
    {
        return new self(
            $channels['h'] ?? $this->h,
            $channels['s'] ?? $this->s,
            $channels['l'] ?? $this->l
        );
    }

    public function __toString(): string
    {
        return sprintf('hsl(%d, %d%%, %d%%)', $this->h, $this->s, $this->l);
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
