<?php

declare(strict_types=1);

namespace Negarity\Color\ColorSpace;

use Negarity\Color\Exception\InvalidColorValueException;

final class CMYK extends AbstractColorSpace
{
    public function __construct(
        private readonly int $c,
        private readonly int $m,
        private readonly int $y,
        private readonly int $k
    ) {
        $this->assertRange($c, 'c');
        $this->assertRange($m, 'm');
        $this->assertRange($y, 'y');
        $this->assertRange($k, 'k');
    }

    public function getName(): string
    {
        return 'cmyk';
    }

    public function getChannels(): array
    {
        return ['c', 'm', 'y', 'k'];
    }

    public function getChannel(string $name): int
    {
        return match ($name) {
            'c' => $this->c,
            'm' => $this->m,
            'y' => $this->y,
            'k' => $this->k,
            default => throw new InvalidColorValueException("Unknown channel: $name"),
        };
    }

    public function toArray(): array
    {
        return ['c' => $this->c, 'm' => $this->m, 'y' => $this->y, 'k' => $this->k];
    }

    public function without(array $channels): static
    {
        return new self(
            in_array('c', $channels, true) ? 0 : $this->c,
            in_array('m', $channels, true) ? 0 : $this->m,
            in_array('y', $channels, true) ? 0 : $this->y,
            in_array('k', $channels, true) ? 0 : $this->k
        );
    }

    public function with(array $channels): static
    {
        return new self(
            $channels['c'] ?? $this->c,
            $channels['m'] ?? $this->m,
            $channels['y'] ?? $this->y,
            $channels['k'] ?? $this->k
        );
    }

    private function assertRange(int $value, string $channel): void
    {
        if ($value < 0 || $value > 100) {
            throw new InvalidColorValueException(
                sprintf('Channel "%s" must be between 0 and 100, got %d', $channel, $value)
            );
        }
    }
}
