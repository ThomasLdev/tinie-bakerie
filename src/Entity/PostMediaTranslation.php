<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\EntityTranslation;
use App\Entity\Traits\LocalizedEntity;
use App\Entity\Traits\MediaAccessibility;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity]
class PostMediaTranslation implements EntityTranslation
{
    use LocalizedEntity;
    use MediaAccessibility;
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\ManyToOne(targetEntity: PostMedia::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(name: 'translatable_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected PostMedia $translatable;

    public function __toString(): string
    {
        return $this->locale;
    }

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    public function getTranslatable(): PostMedia
    {
        return $this->translatable;
    }

    public function setTranslatable(PostMedia $translatable): PostMediaTranslation
    {
        $this->translatable = $translatable;

        return $this;
    }
}
