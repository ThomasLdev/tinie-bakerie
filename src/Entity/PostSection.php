<?php

namespace App\Entity;

use App\Entity\Contracts\TranslatableEntityInterface;
use App\Repository\PostTranslationSectionRepository;
use App\Services\PostSection\Enum\PostSectionType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: PostTranslationSectionRepository::class)]
class PostSection implements TranslatableEntityInterface
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\ManyToOne(inversedBy: 'sections')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Post $post = null;

    #[ORM\Column(type: Types::INTEGER, nullable: false, options: ['default' => 0])]
    private int $position = 0;

    #[ORM\Column(
        type: 'string',
        enumType: PostSectionType::class,
        options: ['default' => PostSectionType::Default, 'nullable' => false]
    )]
    private PostSectionType $type = PostSectionType::Default;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?PostSectionMedia $media = null;

    /**
     * @var Collection<int, PostSectionTranslation>
     */
    #[ORM\OneToMany(
        targetEntity: PostSectionTranslation::class,
        mappedBy: 'postSection',
        cascade: ['persist', 'remove'],
        fetch: 'EXTRA_LAZY',
        orphanRemoval: true,
    )]
    private Collection $translations;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id ?? null;
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

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function getType(): PostSectionType
    {
        return $this->type;
    }

    public function setType(PostSectionType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getMedia(): ?PostSectionMedia
    {
        return $this->media;
    }

    public function setMedia(?PostSectionMedia $media): static
    {
        $this->media = $media;

        return $this;
    }

    /**
     * @return Collection<int, PostSectionTranslation>
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function addTranslation(PostSectionTranslation $translation): static
    {
        if (!$this->translations->contains($translation)) {
            $this->translations->add($translation);
            $translation->setPostSection($this);
        }

        return $this;
    }

    public function removeTranslation(PostSectionTranslation $translation): static
    {
        if ($this->translations->removeElement($translation)) {
            // set the owning side to null (unless already changed)
            if ($translation->getPostSection() === $this) {
                $translation->setPostSection(null);
            }
        }

        return $this;
    }
}
