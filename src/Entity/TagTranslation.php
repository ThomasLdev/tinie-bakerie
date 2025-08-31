<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\EntityTranslation;
use App\Entity\Traits\LocalizedEntity;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity]
#[ORM\UniqueConstraint(name: 'tag_translation_unique_idx', columns: ['locale', 'title'])]
class TagTranslation implements EntityTranslation
{
    use TimestampableEntity;
    use LocalizedEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Tag::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(name: 'translatable_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected Tag $translatable;

    #[ORM\Column(type: Types::STRING)]
    private string $title;

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    public function getTranslatable(): Tag
    {
        return $this->translatable;
    }

    public function setTranslatable(Tag $translatable): TagTranslation
    {
        $this->translatable = $translatable;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): TagTranslation
    {
        $this->title = $title;

        return $this;
    }
}
