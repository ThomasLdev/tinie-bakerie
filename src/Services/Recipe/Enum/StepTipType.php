<?php

declare(strict_types=1);

namespace App\Services\Recipe\Enum;

enum StepTipType: string
{
    case Tip = 'tip';
    case Warning = 'warning';
}
