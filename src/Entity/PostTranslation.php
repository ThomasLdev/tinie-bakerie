<?php

namespace App\Entity;

use App\Entity\Trait\LocalizedEntity;
use App\Repository\PostTranslationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\Slug;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: PostTranslationRepository::class)]
class PostTranslation
{
    use TimestampableEntity, LocalizedEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\ManyToOne(inversedBy: 'translations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Post $post = null;

    #[ORM\Column(length: 255)]
    #[Slug(fields: ['title'])]
    private ?string $slug = null;

    /**
     * @var Collection<int, PostTranslationSection>
     */
    #[ORM\OneToMany(
        targetEntity: PostTranslationSection::class,
        mappedBy: 'translation',
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    private Collection $sections;

    public function __construct()
    {
        $this->sections = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(?Post $post): static
    {
        $this->post = $post;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return Collection<int, PostTranslationSection>
     */
    public function getSections(): Collection
    {
        return $this->sections;
    }

    public function addSection(PostTranslationSection $section): static
    {
        if (!$this->sections->contains($section)) {
            $this->sections->add($section);
            $section->setTranslation($this);
        }

        return $this;
    }

    public function removeSection(PostTranslationSection $section): static
    {
        if ($this->sections->removeElement($section)) {
            // set the owning side to null (unless already changed)
            if ($section->getTranslation() === $this) {
                $section->setTranslation(null);
            }
        }

        return $this;
    }
}
