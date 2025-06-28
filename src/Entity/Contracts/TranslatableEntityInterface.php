<?php

namespace App\Entity\Contracts;

interface TranslatableEntityInterface
{
    public function getTranslations(): iterable;
}
