<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\IsTranslation;
use App\Entity\Traits\Localized;
use App\Entity\Traits\MediaAccessibility;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @implements IsTranslation<PostMedia>
 */
#[ORM\Entity]
class PostMediaTranslation implements IsTranslation, \Stringable
{
    use Localized;
    use MediaAccessibility;
    use TimestampableEntity;

    #[ORM\ManyToOne(targetEntity: PostMedia::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(name: 'translatable_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected PostMedia $translatable;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

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

    public function setTranslatable(PostMedia $translatable): self
    {
        $this->translatable = $translatable;

        return $this;
    }
}
