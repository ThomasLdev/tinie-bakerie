<?php

declare(strict_types=1);

namespace App\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class DurationExtension extends AbstractExtension
{
    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('duration', $this->formatDuration(...)),
        ];
    }

    /**
     * Formats minutes into a human-readable cooking duration.
     * 25 → "25 min" / 60 → "1 h" / 70 → "1 h 10" / 0 → null.
     */
    public function formatDuration(?int $minutes): ?string
    {
        if ($minutes === null || $minutes <= 0) {
            return null;
        }

        if ($minutes < 60) {
            return sprintf('%d min', $minutes);
        }

        $hours = intdiv($minutes, 60);
        $remainder = $minutes % 60;

        if ($remainder === 0) {
            return sprintf('%d h', $hours);
        }

        return sprintf('%d h %d', $hours, $remainder);
    }
}
