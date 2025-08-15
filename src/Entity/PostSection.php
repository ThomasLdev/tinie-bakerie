<?php

namespace App\Entity;

use App\Entity\Contracts\LocalizedEntityInterface;
use App\Entity\Traits\LocalizedEntity;
use App\Services\PostSection\Enum\PostSectionType;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\Mapping\Annotation as Gedmo;

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

    #[Gedmo\Translatable]
    #[ORM\Column(type: Types::TEXT, nullable: false, options: ['default' => ''])]
    private string $content = '';

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
