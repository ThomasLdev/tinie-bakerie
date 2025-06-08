<?php

namespace App\Entity;

use App\Repository\PostRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: PostRepository::class)]
#[Vich\Uploadable]
class Post
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    private ?Category $category = null;

    /**
     * @var Collection<int, PostTranslation>
     */
    #[ORM\OneToMany(
        targetEntity: PostTranslation::class,
        mappedBy: 'post',
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    private Collection $translations;

    /**
     * @var Collection<int, PostTag>
     */
    #[ORM\OneToMany(targetEntity: PostTag::class, mappedBy: 'post')]
    private Collection $tags;

    #[Vich\UploadableField(mapping: 'post_thumbnail', fileNameProperty: 'imageName')]
    private ?File $imageFile = null;

    #[ORM\Column(nullable: true)]
    private ?string $imageName = null;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection<int, PostTranslation>
     */
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

    public function removePostTranslation(PostTranslation $translations): static
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
            $tag->setPost($this);
        }

        return $this;
    }

    public function removeTag(PostTag $tag): static
    {
        if ($this->tags->removeElement($tag)) {
            // set the owning side to null (unless already changed)
            if ($tag->getPost() === $this) {
                $tag->setPost(null);
            }
        }

        return $this;
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageFile(?File $imageFile = null): static
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new DateTimeImmutable();
        }

        return $this;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageName(?string $imageName): static
    {
        $this->imageName = $imageName;

        return $this;
    }
}
