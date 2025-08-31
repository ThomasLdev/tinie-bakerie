<?php

declare(strict_types=1);

namespace App\Entity\Contracts;

interface EntityTranslation {
    public function getTranslatable(): LocalizedEntityInterface;
    public function getLocale(): string;
}
