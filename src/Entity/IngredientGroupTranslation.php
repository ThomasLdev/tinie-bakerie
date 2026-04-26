<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\Translation;
use App\Entity\Traits\Localized;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @implements Translation<IngredientGroup>
 */
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class IngredientGroupTranslation implements Translation, \Stringable
{
    use Localized;
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\ManyToOne(targetEntity: IngredientGroup::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(name: 'translatable_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?IngredientGroup $translatable = null;

    #[ORM\Column(type: Types::STRING, options: ['default' => ''])]
    private string $label = '';

    public function __toString(): string
    {
        return $this->locale;
    }

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    public function getTranslatable(): ?IngredientGroup
    {
        return $this->translatable;
    }

    public function setTranslatable(IngredientGroup $translatable): self
    {
        $this->translatable = $translatable;

        return $this;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    #[ORM\PreUpdate]
    #[ORM\PrePersist]
    public function updateParentTimestamp(): void
    {
        if ($this->translatable instanceof IngredientGroup) {
            $this->translatable->setUpdatedAt(new \DateTime());
        }
    }
}
