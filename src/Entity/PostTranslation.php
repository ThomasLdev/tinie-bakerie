<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\Sluggable;
use App\Entity\Contracts\Translation;
use App\Entity\Traits\Localized;
use App\Entity\Traits\SlugTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @implements Translation<Post>
 */
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\InheritanceType('JOINED')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string', length: 16)]
#[ORM\DiscriminatorMap(['post' => PostTranslation::class, 'recipe' => RecipeTranslation::class])]
#[ORM\UniqueConstraint(name: 'post_translation_lookup_unique_idx', columns: ['locale', 'title'])]
class PostTranslation implements Translation, Sluggable, \Stringable
{
    use Localized;
    use SlugTrait;
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Post::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(name: 'translatable_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected ?Post $translatable = null;

    #[ORM\Column(type: Types::STRING, options: ['default' => ''])]
    private string $title = '';

    #[ORM\Column(type: Types::TEXT, options: ['default' => ''])]
    private string $metaDescription = '';

    #[ORM\Column(type: Types::STRING, length: 60, options: ['default' => ''])]
    private string $metaTitle = '';

    #[ORM\Column(type: Types::TEXT, options: ['default' => ''])]
    private string $excerpt = '';

    public function __toString(): string
    {
        return $this->locale ?? '';
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

    #[ORM\PreUpdate]
    #[ORM\PrePersist]
    public function updateParentTimestamp(): void
    {
        if ($this->translatable instanceof Post) {
            $this->translatable->setUpdatedAt(new \DateTime());
        }
    }
}
