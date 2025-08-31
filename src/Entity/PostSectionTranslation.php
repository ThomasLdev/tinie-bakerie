<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\EntityTranslation;
use App\Entity\Traits\LocalizedEntity;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity]
class PostSectionTranslation implements EntityTranslation
{
    use LocalizedEntity;
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\ManyToOne(targetEntity: PostSection::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(name: 'translatable_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected PostSection $translatable;

    #[ORM\Column(type: Types::TEXT, nullable: false, options: ['default' => ''])]
    private string $content = '';

    #[ORM\Column(type: Types::STRING, nullable: false, options: ['default' => ''])]
    private string $title = '';

    public function __toString(): string
    {
        return $this->locale;
    }

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    public function getTranslatable(): PostSection
    {
        return $this->translatable;
    }

    public function setTranslatable(PostSection $translatable): PostSectionTranslation
    {
        $this->translatable = $translatable;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): PostSectionTranslation
    {
        $this->content = $content;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): PostSectionTranslation
    {
        $this->title = $title;
        return $this;
    }
}
