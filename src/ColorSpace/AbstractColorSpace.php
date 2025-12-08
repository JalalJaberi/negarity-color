<?php

declare(strict_types=1);

namespace Negarity\Color\ColorSpace;

use Negarity\Color\Exception\InvalidColorValueException;

abstract class AbstractColorSpace implements ColorSpaceInterface, \JsonSerializable
{
    abstract public function getName(): string;
    abstract public function getChannels(): array;
    abstract public function getChannel(string $name): float|int;
    abstract public function toArray(): array;
    abstract public function with(array $channels): static;
    abstract public function without(array $channels): static;

    // magic function for get{ChannelName}() calls
    public function __call(string $name, array $arguments): float|int
    {
        if (str_starts_with($name, 'get')) {
            $channelName = lcfirst(substr($name, 3));
            return $this->getChannel($channelName);
        }

        throw new \BadMethodCallException("Method {$name} does not exist.");
    }

    public function __toString(): string
    {
        $channelValues = array_map(
            fn ($channel) => $this->getChannel($channel),
            $this->getChannels()
        );

        return sprintf(
            '%s(%s)',
            $this->getName(),
            implode(', ', $channelValues)
        );
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
