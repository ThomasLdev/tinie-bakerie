<?php

namespace App\Entity;

use App\Entity\Contracts\LocalizedEntityInterface;
use App\Entity\Traits\LocalizedEntity;
use App\Services\PostSection\Enum\PostSectionType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity]
class PostSection implements LocalizedEntityInterface
{
    use TimestampableEntity;
    use LocalizedEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\ManyToOne(inversedBy: 'sections')]
    #[ORM\JoinColumn]
    private ?Post $post = null;

    #[ORM\Column(type: Types::INTEGER, nullable: false, options: ['default' => 0])]
    private int $position = 0;

    #[ORM\Column(
        type: 'string',
        enumType: PostSectionType::class,
        options: ['default' => PostSectionType::Default, 'nullable' => false]
    )]
    private PostSectionType $type = PostSectionType::Default;

    /**
     * @var Collection<int,PostSectionMedia>
     */
    #[ORM\OneToMany(
        targetEntity: PostSectionMedia::class,
        mappedBy: 'postSection',
        cascade: ['persist', 'remove'],
        fetch: 'EXTRA_LAZY',
        orphanRemoval: true,
    )]
    private Collection $media;

    #[Gedmo\Translatable]
    #[ORM\Column(type: Types::TEXT, nullable: false, options: ['default' => ''])]
    private string $content = '';

    public function __construct()
    {
        $this->media = new ArrayCollection();
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

    /**
     * @param array<array-key,PostSectionMedia> $media
     */
    public function setMedia(array $media): self
    {
        foreach ($media as $medium) {
            $this->addMedium($medium);
        }

        return $this;
    }

    /**
     * @return Collection<int,PostSectionMedia>
     */
    public function getMedia(): Collection
    {
        return $this->media;
    }

    public function addMedium(PostSectionMedia $medium): self
    {
        if (!$this->media->contains($medium)) {
            $this->media->add($medium);
            $medium->setPostSection($this);
        }

        return $this;
    }

    public function removeMedium(PostSectionMedia $medium): self
    {
        if ($this->media->removeElement($medium)) {
            // set the owning side to null (unless already changed)
            if ($medium->getPostSection() === $this) {
                $medium->setPostSection(null);
            }
        }

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }
}
