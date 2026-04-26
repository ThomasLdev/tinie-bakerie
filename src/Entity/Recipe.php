<?php

declare(strict_types=1);

namespace App\Entity;

use App\Services\Post\Enum\Difficulty;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Recipe extends Post
{
    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
    private int $cookingTime = 0;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
    private int $preparationTime = 0;

    #[ORM\Column(enumType: Difficulty::class, options: ['default' => Difficulty::Easy])]
    private Difficulty $difficulty = Difficulty::Easy;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 4])]
    private int $servings = 4;

    /** @var Collection<int,IngredientGroup> */
    #[ORM\OneToMany(
        targetEntity: IngredientGroup::class,
        mappedBy: 'recipe',
        cascade: ['persist', 'remove'],
        fetch: 'EXTRA_LAZY',
        orphanRemoval: true,
    )]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $ingredientGroups;

    public function __construct()
    {
        parent::__construct();
        $this->ingredientGroups = new ArrayCollection();
    }

    public function getCookingTime(): int
    {
        return $this->cookingTime;
    }

    public function setCookingTime(int $cookingTime): self
    {
        $this->cookingTime = $cookingTime;

        return $this;
    }

    public function getPreparationTime(): int
    {
        return $this->preparationTime;
    }

    public function setPreparationTime(int $preparationTime): self
    {
        $this->preparationTime = $preparationTime;

        return $this;
    }

    public function getDifficulty(): Difficulty
    {
        return $this->difficulty;
    }

    public function setDifficulty(Difficulty $difficulty): self
    {
        $this->difficulty = $difficulty;

        return $this;
    }

    public function getServings(): int
    {
        return $this->servings;
    }

    public function setServings(int $servings): self
    {
        $this->servings = $servings;

        return $this;
    }

    public function getTotalRecipeTime(): int
    {
        return $this->cookingTime + $this->preparationTime;
    }

    /**
     * @param IngredientGroup[] $groups
     */
    public function setIngredientGroups(array $groups): self
    {
        foreach ($groups as $group) {
            $this->addIngredientGroup($group);
        }

        return $this;
    }

    /**
     * @return Collection<int,IngredientGroup>
     */
    public function getIngredientGroups(): Collection
    {
        return $this->ingredientGroups;
    }

    public function addIngredientGroup(IngredientGroup $group): self
    {
        if (!$this->ingredientGroups->contains($group)) {
            $this->ingredientGroups->add($group);
            $group->setRecipe($this);
        }

        return $this;
    }

    public function removeIngredientGroup(IngredientGroup $group): self
    {
        if ($this->ingredientGroups->removeElement($group) && $group->getRecipe() === $this) {
            $group->setRecipe(null);
        }

        return $this;
    }

    /**
     * Recipe steps live in the inherited Post::$sections collection (PostSection rows
     * with discriminator `recipe_step`). This helper filters and exposes them typed.
     *
     * @return Collection<int,RecipeStep>
     */
    public function getSteps(): Collection
    {
        /** @var Collection<int,RecipeStep> $steps */
        $steps = $this->getSections()->filter(
            static fn (PostSection $section): bool => $section instanceof RecipeStep,
        );

        return $steps;
    }

    /**
     * @return Collection<int,PostSection>
     */
    public function getNarrativeSections(): Collection
    {
        return $this->getSections()->filter(
            static fn (PostSection $section): bool => !$section instanceof RecipeStep,
        );
    }

    public function getStepCount(): int
    {
        return $this->getSteps()->count();
    }

    public function getIngredientCount(): int
    {
        $count = 0;

        foreach ($this->ingredientGroups as $group) {
            $count += $group->getIngredients()->count();
        }

        return $count;
    }

    #[\Override]
    public function getCurrentTranslation(): ?RecipeTranslation
    {
        $translation = $this->getTranslationForCurrentLocale();

        return $translation instanceof RecipeTranslation ? $translation : null;
    }

    public function getNotes(): string
    {
        return $this->getCurrentTranslation()?->getNotes() ?? '';
    }

    public function getChefNoteTitle(): string
    {
        return $this->getCurrentTranslation()?->getChefNoteTitle() ?? '';
    }
}
