<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\Positionable;
use App\Entity\Contracts\Translatable;
use App\Entity\Traits\TranslationAccessorTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @implements Translatable<IngredientTranslation>
 */
#[ORM\Entity]
class Ingredient implements Translatable, Positionable, \Stringable
{
    use TimestampableEntity;

    /** @use TranslationAccessorTrait<IngredientTranslation> */
    use TranslationAccessorTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\ManyToOne(targetEntity: IngredientGroup::class, inversedBy: 'ingredients')]
    #[ORM\JoinColumn(name: 'group_id', nullable: true, onDelete: 'CASCADE')]
    private ?IngredientGroup $group = null;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
    private int $position = 0;

    /**
     * Numeric quantity for items recomputable when the user changes servings client-side.
     * Null when the recipe ingredient has no scalable amount (e.g. "1 zeste de citron").
     */
    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $baseQuantity = null;

    /** @var Collection<int,IngredientTranslation> */
    #[ORM\OneToMany(
        targetEntity: IngredientTranslation::class,
        mappedBy: 'translatable',
        cascade: ['persist', 'remove'],
        orphanRemoval: true,
    )]
    private Collection $translations;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getName();
    }

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    public function getGroup(): ?IngredientGroup
    {
        return $this->group;
    }

    public function setGroup(?IngredientGroup $group): self
    {
        $this->group = $group;

        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getBaseQuantity(): ?float
    {
        return $this->baseQuantity;
    }

    public function setBaseQuantity(?float $baseQuantity): self
    {
        $this->baseQuantity = $baseQuantity;

        return $this;
    }

    /**
     * @return Collection<int,IngredientTranslation>
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function addTranslation(IngredientTranslation $translation): self
    {
        if (!$this->translations->contains($translation)) {
            $this->translations[] = $translation;
            $translation->setTranslatable($this);
        }

        return $this;
    }

    public function removeTranslation(IngredientTranslation $translation): self
    {
        $this->translations->removeElement($translation);

        return $this;
    }

    public function getCurrentTranslation(): ?IngredientTranslation
    {
        $translation = $this->getTranslationForCurrentLocale();

        return $translation instanceof IngredientTranslation ? $translation : null;
    }

    public function getName(): string
    {
        return $this->getCurrentTranslation()?->getName() ?? '';
    }

    public function getUnit(): string
    {
        return $this->getCurrentTranslation()?->getUnit() ?? '';
    }

    public function getQuantityDisplay(): string
    {
        return $this->getCurrentTranslation()?->getQuantityDisplay() ?? '';
    }
}
