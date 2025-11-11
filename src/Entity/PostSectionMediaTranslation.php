<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\Translation;
use App\Entity\Traits\Localized;
use App\Entity\Traits\MediaAccessibility;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @implements Translation<PostSectionMedia>
 */
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class PostSectionMediaTranslation implements Translation, \Stringable
{
    use Localized;
    use MediaAccessibility;
    use TimestampableEntity;

    #[ORM\ManyToOne(targetEntity: PostSectionMedia::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(name: 'translatable_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected ?PostSectionMedia $translatable = null;

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

    public function getTranslatable(): ?PostSectionMedia
    {
        return $this->translatable;
    }

    public function setTranslatable(PostSectionMedia $translatable): self
    {
        $this->translatable = $translatable;

        return $this;
    }

    #[ORM\PreUpdate]
    #[ORM\PrePersist]
    public function updateParentTimestamp(): void
    {
        if ($this->translatable instanceof PostSectionMedia) {
            $this->translatable->setUpdatedAt(new \DateTime());
        }
    }
}
