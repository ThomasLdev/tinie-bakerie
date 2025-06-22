<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\Translator\TranslationInterface;

#[ORM\Entity(repositoryClass: PostRepository::class)]
class Post implements TranslatableEntityInterface
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    private ?Category $category = null;

    #[ORM\OneToMany(
        targetEntity: PostTranslation::class,
        mappedBy: 'post',
        cascade: ['persist', 'remove'],
        fetch: 'EXTRA_LAZY',
        orphanRemoval: true
    )]
    private Collection $translations;

    /**
     * @var Collection<int, PostTag>
     */
    #[ORM\ManyToMany(
        targetEntity: PostTag::class,
        inversedBy: 'posts',
        cascade: ['persist'],
        fetch: 'EXTRA_LAZY',
    )]
    private Collection $tags;

    /**
     * @var Collection<int, PostMedia>
     */
    #[ORM\OneToMany(
        targetEntity: PostMedia::class,
        mappedBy: 'post',
        cascade: ['persist', 'remove'],
        fetch: 'EXTRA_LAZY',
        orphanRemoval: true,
    )]
    private Collection $media;

    /**
     * @var Collection<int, PostSection>
     */
    #[ORM\OneToMany(
        targetEntity: PostSection::class,
        mappedBy: 'post',
        cascade: ['persist', 'remove'],
        fetch: 'EXTRA_LAZY',
        orphanRemoval: true,
    )]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $sections;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->media = new ArrayCollection();
        $this->sections = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getTranslation(): TranslationInterface
    {
        return $this->translations->first() ?? new PostTranslation();
    }

    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function addTranslation(PostTranslation $translations): static
    {
        if (!$this->translations->contains($translations)) {
            $this->translations->add($translations);
            $translations->setPost($this);
        }

        return $this;
    }

    public function removeTranslation(PostTranslation $translations): static
    {
        if ($this->translations->removeElement($translations)) {
            // set the owning side to null (unless already changed)
            if ($translations->getPost() === $this) {
                $translations->setPost(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PostTag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(PostTag $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }

        return $this;
    }

    public function removeTag(PostTag $tag): static
    {
        $this->tags->removeElement($tag);

        return $this;
    }

    /**
     * @return Collection<int, PostMedia>
     */
    public function getMedia(): Collection
    {
        return $this->media;
    }

    public function addMedium(PostMedia $medium): static
    {
        if (!$this->media->contains($medium)) {
            $this->media->add($medium);
            $medium->setPost($this);
        }

        return $this;
    }

    public function removeMedium(PostMedia $medium): static
    {
        if ($this->media->removeElement($medium)) {
            // set the owning side to null (unless already changed)
            if ($medium->getPost() === $this) {
                $medium->setPost(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PostSection>
     */
    public function getSections(): Collection
    {
        return $this->sections;
    }

    public function addSection(PostSection $section): static
    {
        if (!$this->sections->contains($section)) {
            $this->sections->add($section);
            $section->setPost($this);
        }

        return $this;
    }

    public function removeSection(PostSection $section): static
    {
        if ($this->sections->removeElement($section)) {
            // set the owning side to null (unless already changed)
            if ($section->getPost() === $this) {
                $section->setPost(null);
            }
        }

        return $this;
    }
}
