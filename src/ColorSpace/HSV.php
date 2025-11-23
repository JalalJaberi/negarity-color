<?php

declare(strict_types=1);

namespace Negarity\Color\ColorSpace;

use Negarity\Color\Exception\InvalidColorValueException;

final class HSV extends AbstractColorSpace
{
    public function __construct(
        private readonly int $h, // 0–360
        private readonly int $s, // 0–100
        private readonly int $v  // 0–100
    ) {
        $this->assertRange($h, 0, 360, 'h');
        $this->assertRange($s, 0, 100, 's');
        $this->assertRange($v, 0, 100, 'v');
    }

    public function getName(): string
    {
        return 'hsv';
    }

    public function getChannels(): array
    {
        return ['h', 's', 'v'];
    }

    public function getChannel(string $name): int
    {
        return match ($name) {
            'h' => $this->h,
            's' => $this->s,
            'v' => $this->v,
            default => throw new InvalidColorValueException("Unknown channel: $name"),
        };
    }

    public function toArray(): array
    {
        return ['h' => $this->h, 's' => $this->s, 'v' => $this->v];
    }

    public function without(array $channels): static
    {
        return new self(
            in_array('h', $channels, true) ? 0 : $this->h,
            in_array('s', $channels, true) ? 0 : $this->s,
            in_array('v', $channels, true) ? 0 : $this->v
        );
    }

    public function with(array $channels): static
    {
        return new self(
            $channels['h'] ?? $this->h,
            $channels['s'] ?? $this->s,
            $channels['v'] ?? $this->v
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
