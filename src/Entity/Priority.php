<?php

namespace App\Entity;

enum Priority : string
{
    case Low = 'Low';
    case Medium = 'Medium';
    case High = 'High';

    public function label(): string
    {
        return match ($this) {
            self::Low => 'Low Priority',
            self::Medium => 'Medium Priority',
            self::High => 'High Priority',
        };
    }

    public function days(): int
    {
        return match ($this) {
            self::Low => 30,
            self::Medium => 15,
            self::High => 7,
        };
    }
}