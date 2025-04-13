<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use InvalidArgumentException;

final class TeamStrength
{
    private int $value;

    public function __construct(int $value)
    {
        // Team strength should be between 1 and 100
        if ($value < 1 || $value > 100) {
            throw new InvalidArgumentException('Team strength must be between 1 and 100');
        }

        $this->value = $value;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public static function fromValue(int $value): self
    {
        return new self($value);
    }

    public static function createLow(): self
    {
        return new self(mt_rand(40, 60));
    }

    public static function createMedium(): self
    {
        return new self(mt_rand(61, 80));
    }

    public static function createHigh(): self
    {
        return new self(mt_rand(81, 100));
    }

    public static function fromString(string $level): self
    {
        return match (strtolower($level)) {
            'low' => self::createLow(),
            'medium' => self::createMedium(),
            'high' => self::createHigh(),
            default => throw new InvalidArgumentException('Invalid strength level. Use "low", "medium", or "high"'),
        };
    }
}