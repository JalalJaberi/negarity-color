<?php

declare(strict_types=1);

namespace Negarity\Color\ColorSpace;

use Negarity\Color\Exception\InvalidColorValueException;

final class XYZ extends AbstractColorSpace
{
    public function __construct(
        private readonly int $x,
        private readonly int $y,
        private readonly int $z
    ) {
        $this->assertRange($x, 0, 100, 'x');
        $this->assertRange($y, 0, 100, 'y');
        $this->assertRange($z, 0, 100, 'z');
    }

    public function getName(): string
    {
        return 'xyz';
    }

    public function getChannels(): array
    {
        return ['x', 'y', 'z'];
    }

    public function getChannel(string $name): int
    {
        return match ($name) {
            'x' => $this->x,
            'y' => $this->y,
            'z' => $this->z,
            default => throw new InvalidColorValueException("Unknown channel: $name"),
        };
    }

    public function toArray(): array
    {
        return ['x' => $this->x, 'y' => $this->y, 'z' => $this->z];
    }

    public function without(array $channels): static
    {
        return new self(
            in_array('x', $channels, true) ? 0 : $this->x,
            in_array('y', $channels, true) ? 0 : $this->y,
            in_array('z', $channels, true) ? 0 : $this->z
        );
    }

    public function with(array $channels): static
    {
        return new self(
            $channels['x'] ?? $this->x,
            $channels['y'] ?? $this->y,
            $channels['z'] ?? $this->z
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
