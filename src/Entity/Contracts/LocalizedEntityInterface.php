<?php

declare(strict_types=1);

namespace App\Entity\Contracts;

use Doctrine\Common\Collections\Collection;

interface LocalizedEntityInterface
{
    public function addTranslation(EntityTranslation $translation): self;

    /**
     * @return Collection<int, EntityTranslation>
     */
    public function getTranslations(): Collection;
}
