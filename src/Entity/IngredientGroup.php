<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\Translatable;
use App\Entity\Traits\TranslationAccessorTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @implements Translatable<IngredientGroupTranslation>
 */
#[ORM\Entity]
class IngredientGroup implements Translatable, \Stringable
{
    use TimestampableEntity;

    /** @use TranslationAccessorTrait<IngredientGroupTranslation> */
    use TranslationAccessorTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Recipe::class, inversedBy: 'ingredientGroups')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?Recipe $recipe = null;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
    private int $position = 0;

    /** @var Collection<int,Ingredient> */
    #[ORM\OneToMany(
        targetEntity: Ingredient::class,
        mappedBy: 'group',
        cascade: ['persist', 'remove'],
        fetch: 'EXTRA_LAZY',
        orphanRemoval: true,
    )]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $ingredients;

    /** @var Collection<int,IngredientGroupTranslation> */
    #[ORM\OneToMany(
        targetEntity: IngredientGroupTranslation::class,
        mappedBy: 'translatable',
        cascade: ['persist', 'remove'],
        orphanRemoval: true,
    )]
    private Collection $translations;

    public function __construct()
    {
        $this->ingredients = new ArrayCollection();
        $this->translations = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getLabel();
    }

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    public function getRecipe(): ?Recipe
    {
        return $this->recipe;
    }

    public function setRecipe(?Recipe $recipe): self
    {
        $this->recipe = $recipe;

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

    /**
     * @param Ingredient[] $ingredients
     */
    public function setIngredients(array $ingredients): self
    {
        foreach ($ingredients as $ingredient) {
            $this->addIngredient($ingredient);
        }

        return $this;
    }

    /**
     * @return Collection<int,Ingredient>
     */
    public function getIngredients(): Collection
    {
        return $this->ingredients;
    }

    public function addIngredient(Ingredient $ingredient): self
    {
        if (!$this->ingredients->contains($ingredient)) {
            $this->ingredients->add($ingredient);
            $ingredient->setGroup($this);
        }

        return $this;
    }

    public function removeIngredient(Ingredient $ingredient): self
    {
        if ($this->ingredients->removeElement($ingredient) && $ingredient->getGroup() === $this) {
            $ingredient->setGroup(null);
        }

        return $this;
    }

    /**
     * @return Collection<int,IngredientGroupTranslation>
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function addTranslation(IngredientGroupTranslation $translation): self
    {
        if (!$this->translations->contains($translation)) {
            $this->translations[] = $translation;
            $translation->setTranslatable($this);
        }

        return $this;
    }

    public function removeTranslation(IngredientGroupTranslation $translation): self
    {
        $this->translations->removeElement($translation);

        return $this;
    }

    public function getCurrentTranslation(): ?IngredientGroupTranslation
    {
        $translation = $this->getTranslationForCurrentLocale();

        return $translation instanceof IngredientGroupTranslation ? $translation : null;
    }

    public function getLabel(): string
    {
        return $this->getCurrentTranslation()?->getLabel() ?? '';
    }
}
