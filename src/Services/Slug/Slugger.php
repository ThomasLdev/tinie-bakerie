<?php

declare(strict_types=1);

namespace App\Services\Slug;

use Symfony\Component\String\Slugger\AsciiSlugger;

class Slugger
{
    public function slugify(string $text): string
    {
        return new AsciiSlugger()->slug($text)->lower()->toString();
    }
}
