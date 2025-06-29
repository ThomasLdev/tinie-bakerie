<?php

namespace App\Entity;

use App\Entity\Traits\LocalizedEntity;
use App\Repository\PostSectionTranslationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: PostSectionTranslationRepository::class)]
class PostSectionTranslation
{
    use TimestampableEntity;
    use LocalizedEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(type: Types::TEXT, nullable: false, options: ['default' => ''])]
    private string $content = '';

    #[ORM\ManyToOne(inversedBy: 'translations')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?PostSection $postSection = null;

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getPostSection(): ?PostSection
    {
        return $this->postSection;
    }

    public function setPostSection(?PostSection $postSection): static
    {
        $this->postSection = $postSection;

        return $this;
    }
}
