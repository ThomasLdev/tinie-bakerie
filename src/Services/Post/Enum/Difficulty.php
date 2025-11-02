<?php

declare(strict_types=1);

namespace App\Services\Post\Enum;

enum Difficulty: string
{
    case Easy = 'easy';
    case Medium = 'medium';
    case Advanced = 'advanced';
}
