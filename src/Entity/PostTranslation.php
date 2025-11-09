<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\HasSlugs;
use App\Entity\Contracts\Translation;
use App\Entity\Traits\Localized;
use App\Entity\Traits\Sluggable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @implements Translation<Post>
 */
#[ORM\Entity]
#[ORM\UniqueConstraint(name: 'post_translation_lookup_unique_idx', columns: ['locale', 'title'])]
class PostTranslation implements Translation, HasSlugs, \Stringable
{
    use Localized;
    use Sluggable;
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Post::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(name: 'translatable_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Post $translatable = null;

    #[ORM\Column(type: Types::STRING, options: ['default' => ''])]
    private string $title = '';

    #[ORM\Column(type: Types::TEXT, options: ['default' => ''])]
    private string $metaDescription = '';

    #[ORM\Column(type: Types::STRING, length: 60, options: ['default' => ''])]
    private string $metaTitle = '';

    #[ORM\Column(type: Types::TEXT, options: ['default' => ''])]
    private string $excerpt = '';

    #[ORM\Column(type: Types::TEXT, options: ['default' => ''])]
    private string $notes = '';

    public function __toString(): string
    {
        return $this->locale;
    }

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    public function getTranslatable(): ?Post
    {
        return $this->translatable;
    }

    public function setTranslatable(Post $translatable): self
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

    public function getNotes(): string
    {
        return $this->notes;
    }

    public function setNotes(string $notes): self
    {
        $this->notes = $notes;

        return $this;
    }
}
