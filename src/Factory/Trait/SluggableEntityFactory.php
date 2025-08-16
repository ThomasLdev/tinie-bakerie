<?php

declare(strict_types=1);

namespace App\Factory\Trait;

use Symfony\Component\String\Slugger\AsciiSlugger;

trait SluggableEntityFactory
{
    protected function createSlug(string $value): string
    {
        return new AsciiSlugger()->slug($value)->lower()->toString();
    }
}
