<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\IsTranslation;
use App\Entity\Traits\Localized;
use App\Entity\Traits\MediaAccessibility;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @implements IsTranslation<CategoryMedia>
 */
#[ORM\Entity]
class CategoryMediaTranslation implements IsTranslation
{
    use Localized;
    use MediaAccessibility;
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\ManyToOne(targetEntity: CategoryMedia::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(name: 'translatable_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private CategoryMedia $translatable;

    public function __toString(): string
    {
        return $this->locale;
    }

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    public function getTranslatable(): CategoryMedia
    {
        return $this->translatable;
    }

    public function setTranslatable(CategoryMedia $translatable): CategoryMediaTranslation
    {
        $this->translatable = $translatable;

        return $this;
    }
}
