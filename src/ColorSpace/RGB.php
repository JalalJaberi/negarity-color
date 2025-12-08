<?php

declare(strict_types=1);

namespace Negarity\Color\ColorSpace;

use Negarity\Color\Exception\InvalidColorValueException;
use Negarity\Color\ColorSpace\ColorSpaceEnum;

final class RGB extends AbstractColorSpace
{
    public function __construct(
        private readonly int $r,
        private readonly int $g,
        private readonly int $b
    ) {
        $this->assertRange($r, 'r');
        $this->assertRange($g, 'g');
        $this->assertRange($b, 'b');
    }

    public function getName(): string
    {
        return ColorSpaceEnum::RGB->value;
    }

    public function getChannels(): array
    {
        return ['r', 'g', 'b'];
    }

    public function getChannel(string $name): int
    {
        return match ($name) {
            'r' => $this->r,
            'g' => $this->g,
            'b' => $this->b,
            default => throw new InvalidColorValueException("Unknown channel: $name"),
        };
    }

    public function toArray(): array
    {
        return ['r' => $this->r, 'g' => $this->g, 'b' => $this->b];
    }

    public function with(array $channels): static
    {
        return new self(
            $channels['r'] ?? $this->r,
            $channels['g'] ?? $this->g,
            $channels['b'] ?? $this->b
        );
    }

    public function without(array $channels): static
    {
        $r = in_array('r', $channels, true) ? 0 : $this->r;
        $g = in_array('g', $channels, true) ? 0 : $this->g;
        $b = in_array('b', $channels, true) ? 0 : $this->b;

        return new self($r, $g, $b);
    }

    private function assertRange(int $value, string $channel): void
    {
        if ($value < 0 || $value > 255) {
            throw new InvalidColorValueException(
                sprintf('Channel "%s" must be between 0 and 255, got %f', $channel, $value)
            );
        }
    }
}
