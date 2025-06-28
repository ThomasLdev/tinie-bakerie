<?php

namespace App\Entity\Contracts;

interface TranslatableEntityInterface
{
    /**
     * @return array<array-key, object>
     */
    public function getTranslations(): iterable;
}
