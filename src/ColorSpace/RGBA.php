<?php

declare(strict_types=1);

namespace Negarity\Color\ColorSpace;

use Negarity\Color\Exception\InvalidColorValueException;

final class RGBA extends AbstractColorSpace
{
    public function __construct(
        private readonly int $r,
        private readonly int $g,
        private readonly int $b,
        private readonly int $a = 255
    ) {
        $this->assertRange($r, 'r');
        $this->assertRange($g, 'g');
        $this->assertRange($b, 'b');
        $this->assertRange($a, 'a');
    }

    public function getName(): string
    {
        return 'rgba';
    }

    public function getChannels(): array
    {
        return ['r', 'g', 'b', 'a'];
    }

    public function getChannel(string $name): int
    {
        return match ($name) {
            'r' => $this->r,
            'g' => $this->g,
            'b' => $this->b,
            'a' => $this->a,
            default => throw new InvalidColorValueException("Unknown channel: $name"),
        };
    }

    public function toArray(): array
    {
        return ['r' => $this->r, 'g' => $this->g, 'b' => $this->b, 'a' => $this->a];
    }

    public function with(array $channels): static
    {
        return new self(
            $channels['r'] ?? $this->r,
            $channels['g'] ?? $this->g,
            $channels['b'] ?? $this->b,
            $channels['a'] ?? $this->a
        );
    }

    public function without(array $channels): static
    {
        return new self(
            in_array('r', $channels, true) ? 0 : $this->r,
            in_array('g', $channels, true) ? 0 : $this->g,
            in_array('b', $channels, true) ? 0 : $this->b,
            in_array('a', $channels, true) ? 0 : $this->a
        );
    }

    public function __toString(): string
    {
        return sprintf('rgba(%d, %d, %d, %.2f)', $this->r, $this->g, $this->b, $this->a / 255);
    }

    private function assertRange(int $value, string $channel): void
    {
        if ($value < 0 || $value > 255) {
            throw new InvalidColorValueException(
                sprintf('Channel "%s" must be between 0 and 255, got %d', $channel, $value)
            );
        }
    }
}
