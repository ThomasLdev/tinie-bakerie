<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\HasSlugs;
use App\Entity\Contracts\IsTranslation;
use App\Entity\Traits\Localized;
use App\Entity\Traits\Sluggable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @implements IsTranslation<Category>
 */
#[ORM\Entity]
#[ORM\UniqueConstraint(name: 'category_translation_unique_idx', columns: ['locale', 'title'])]
class CategoryTranslation implements IsTranslation, HasSlugs
{
    use Localized;
    use Sluggable;
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(name: 'translatable_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Category $translatable;

    #[ORM\Column(type: Types::STRING)]
    private string $title;

    #[ORM\Column(type: Types::STRING, options: ['default' => ''])]
    private string $description = '';

    #[ORM\Column(type: Types::TEXT, options: ['default' => ''])]
    private string $metaDescription = '';

    #[ORM\Column(type: Types::STRING, length: 60, options: ['default' => ''])]
    private string $metaTitle = '';

    #[ORM\Column(type: Types::TEXT, options: ['default' => ''])]
    private string $excerpt = '';

    public function __toString(): string
    {
        return $this->locale;
    }

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    public function getTranslatable(): Category
    {
        return $this->translatable;
    }

    public function setTranslatable(Category $translatable): self
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

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getMetaDescription(): string
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(string $metaDescription): self
    {
        $this->metaDescription = $metaDescription;

        return $this;
    }

    public function getMetaTitle(): string
    {
        return $this->metaTitle;
    }

    public function setMetaTitle(string $metaTitle): self
    {
        $this->metaTitle = $metaTitle;

        return $this;
    }

    public function getExcerpt(): string
    {
        return $this->excerpt;
    }

    public function setExcerpt(string $excerpt): self
    {
        $this->excerpt = $excerpt;

        return $this;
    }
}
