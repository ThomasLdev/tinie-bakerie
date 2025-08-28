<?php

namespace App\Entity;

use App\Entity\Contracts\LocalizedEntityInterface;
use App\Entity\Contracts\SluggableEntityInterface;
use App\Entity\Traits\ActivableEntityTrait;
use App\Entity\Traits\LocalizedEntity;
use App\Entity\Traits\SluggableEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\UniqueConstraint('post_title_unique', ['title'])]
#[ORM\Entity]
class Post implements LocalizedEntityInterface, SluggableEntityInterface
{
    use TimestampableEntity;
    use LocalizedEntity;
    use SluggableEntity;
    use ActivableEntityTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[Gedmo\Translatable]
    #[ORM\Column(type: Types::STRING)]
    private string $title;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    private ?Category $category = null;

    /**
     * @var Collection<int, Tag>
     */
    #[ORM\ManyToMany(
        targetEntity: Tag::class,
        inversedBy: 'posts',
        cascade: ['persist'],
        fetch: 'EXTRA_LAZY',
    )]
    private Collection $tags;

    /**
     * @var Collection<int,PostMedia>
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
     * @var Collection<int,PostSection>
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
        $this->tags = new ArrayCollection();
        $this->media = new ArrayCollection();
        $this->sections = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @param array<array-key,Tag> $tags
     */
    public function setTags(array $tags): self
    {
        foreach ($tags as $tag) {
            $this->addTag($tag);
        }

        return $this;
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        $this->tags->removeElement($tag);

        return $this;
    }

    /**
     * @param array<array-key,PostMedia> $media
     */
    public function setMedia(array $media): self
    {
        foreach ($media as $medium) {
            $this->addMedium($medium);
        }

        return $this;
    }

    /**
     * @return Collection<int,PostMedia>
     */
    public function getMedia(): Collection
    {
        return $this->media;
    }

    public function addMedium(PostMedia $medium): self
    {
        if (!$this->media->contains($medium)) {
            $this->media->add($medium);
            $medium->setPost($this);
        }

        return $this;
    }

    public function removeMedium(PostMedia $medium): self
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
     * @param array<array-key,PostSection> $sections
     */
    public function setSections(array $sections): self
    {
        foreach ($sections as $section) {
            $this->addSection($section);
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

    public function addSection(PostSection $section): self
    {
        if (!$this->sections->contains($section)) {
            $this->sections->add($section);
            $section->setPost($this);
        }

        return $this;
    }

    public function removeSection(PostSection $section): self
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
