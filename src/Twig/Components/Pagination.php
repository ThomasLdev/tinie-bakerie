<?php

declare(strict_types=1);

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Pagination
{
    public int $current = 1;

    public int $total = 1;

    public int $siblings = 1;

    /**
     * Builds a paginated sequence with intelligent ellipses.
     * Examples (siblings=1):
     *  - current=1, total=5  → [1,2,3,4,5]
     *  - current=7, total=12 → [1,'…',6,7,8,'…',12]
     *  - current=2, total=12 → [1,2,3,'…',12]
     *
     * @return list<int|'…'>
     */
    public function getPages(): array
    {
        $total = max(1, $this->total);
        $current = max(1, min($this->current, $total));
        $siblings = max(0, $this->siblings);

        $start = max(2, $current - $siblings);
        $end = min($total - 1, $current + $siblings);

        $pages = [1];

        if ($start > 2) {
            $pages[] = '…';
        }

        for ($i = $start; $i <= $end; ++$i) {
            $pages[] = $i;
        }

        if ($end < $total - 1) {
            $pages[] = '…';
        }

        if ($total > 1) {
            $pages[] = $total;
        }

        return $pages;
    }

    public function isFirst(): bool
    {
        return $this->current <= 1;
    }

    public function isLast(): bool
    {
        return $this->current >= $this->total;
    }
}
