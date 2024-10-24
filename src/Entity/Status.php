<?php

namespace App\Entity;

use bar\baz\source_with_namespace;

enum Status : string
{
    case Open = 'open';
    case InProgress = 'in_progress';
    case Resolved = 'medium';
    case Closed = 'high';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::InProgress => 'In Progress',
            self::Resolved => 'Resolved',
            self::Closed => 'Closed',
        };
    }
}
