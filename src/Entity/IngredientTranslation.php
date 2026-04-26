<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\Translation;
use App\Entity\Traits\Localized;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @implements Translation<Ingredient>
 */
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class IngredientTranslation implements Translation, \Stringable
{
    use Localized;
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Ingredient::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(name: 'translatable_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Ingredient $translatable = null;

    #[ORM\Column(type: Types::STRING, options: ['default' => ''])]
    private string $name = '';

    #[ORM\Column(type: Types::STRING, options: ['default' => ''])]
    private string $unit = '';

    /**
     * Override of the rendered quantity for special cases ("½", "1 zeste de").
     * If empty, the renderer derives the quantity from Ingredient::$baseQuantity scaled by servings.
     */
    #[ORM\Column(type: Types::STRING, options: ['default' => ''])]
    private string $quantityDisplay = '';

    public function __toString(): string
    {
        return $this->locale;
    }

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    public function getTranslatable(): ?Ingredient
    {
        return $this->translatable;
    }

    public function setTranslatable(Ingredient $translatable): self
    {
        $this->translatable = $translatable;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getUnit(): string
    {
        return $this->unit;
    }

    public function setUnit(string $unit): self
    {
        $this->unit = $unit;

        return $this;
    }

    public function getQuantityDisplay(): string
    {
        return $this->quantityDisplay;
    }

    public function setQuantityDisplay(string $quantityDisplay): self
    {
        $this->quantityDisplay = $quantityDisplay;

        return $this;
    }

    #[ORM\PreUpdate]
    #[ORM\PrePersist]
    public function updateParentTimestamp(): void
    {
        if ($this->translatable instanceof Ingredient) {
            $this->translatable->setUpdatedAt(new \DateTime());
        }
    }
}
