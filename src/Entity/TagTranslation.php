<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\IsTranslation;
use App\Entity\Traits\Localized;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @implements IsTranslation<Tag>
 */
#[ORM\Entity]
#[ORM\UniqueConstraint(name: 'tag_translation_unique_idx', columns: ['locale', 'title'])]
class TagTranslation implements IsTranslation, \Stringable
{
    use Localized;
    use TimestampableEntity;

    #[ORM\ManyToOne(targetEntity: Tag::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(name: 'translatable_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected ?Tag $translatable = null;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(type: Types::STRING)]
    private string $title;

    public function __toString(): string
    {
        return $this->locale;
    }

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    public function getTranslatable(): ?Tag
    {
        return $this->translatable;
    }

    public function setTranslatable(Tag $translatable): self
    {
        $this->translatable = $translatable;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }
}
