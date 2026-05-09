<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\Recipe;
use App\Repository\CategoryRepository;
use App\Repository\RecipeRepository;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class RecipesIndex
{
    use DefaultActionTrait;

    private const int PER_PAGE = 11;

    /** @var list<'recent'|'quickest'|'easiest'> */
    private const array SORT_VALUES = ['recent', 'quickest', 'easiest'];

    #[LiveProp(writable: true, url: true)]
    public int $page = 1;

    #[LiveProp(writable: true, url: true)]
    public ?string $category = null;

    #[LiveProp(writable: true, url: true)]
    public string $sort = 'recent';

    /** @var array{items: list<Recipe>, total: int}|null */
    private ?array $cached = null;

    public function __construct(
        private readonly RecipeRepository $recipeRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly TranslatorInterface $translator,
    ) {
    }

    /**
     * @return array{items: list<Recipe>, total: int}
     */
    private function fetch(): array
    {
        if ($this->cached !== null) {
            return $this->cached;
        }

        $sort = \in_array($this->sort, self::SORT_VALUES, true) ? $this->sort : 'recent';
        $page = max(1, $this->page);

        $result = $this->recipeRepository->findActivePaginated(
            page: $page,
            perPage: self::PER_PAGE,
            categorySlug: $this->category,
            sort: $sort,
        );

        // Clamp silently if requested page is beyond bounds.
        if ($result['total'] > 0 && [] === $result['items'] && $page > 1) {
            $result = $this->recipeRepository->findActivePaginated(
                page: 1,
                perPage: self::PER_PAGE,
                categorySlug: $this->category,
                sort: $sort,
            );
            $this->page = 1;
        }

        return $this->cached = $result;
    }

    /**
     * @return list<Recipe>
     */
    public function getFeatured(): array
    {
        return \array_slice($this->fetch()['items'], 0, 4);
    }

    /**
     * @return list<Recipe>
     */
    public function getRecents(): array
    {
        return \array_slice($this->fetch()['items'], 4, 7);
    }

    public function getTotal(): int
    {
        return $this->fetch()['total'];
    }

    public function getTotalPages(): int
    {
        return max(1, (int) ceil($this->getTotal() / self::PER_PAGE));
    }

    /**
     * @return list<array{label: string, slug: ?string, active: bool}>
     */
    public function getChips(): array
    {
        $chips = [[
            'label' => $this->translator->trans('recipes.filters.all'),
            'slug' => null,
            'active' => $this->category === null || $this->category === '',
        ]];

        foreach ($this->categoryRepository->findAllSlugs() as $category) {
            $slug = $category->getSlug();

            if ($slug === '') {
                continue;
            }

            $chips[] = [
                'label' => $category->getTitle(),
                'slug' => $slug,
                'active' => $this->category === $slug,
            ];
        }

        return $chips;
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public function getSortOptions(): array
    {
        return [
            ['value' => 'recent', 'label' => $this->translator->trans('recipes.filters.sort.recent')],
            ['value' => 'quickest', 'label' => $this->translator->trans('recipes.filters.sort.quickest')],
            ['value' => 'easiest', 'label' => $this->translator->trans('recipes.filters.sort.easiest')],
        ];
    }

    #[LiveAction]
    public function selectCategory(#[LiveArg] ?string $slug = null): void
    {
        $this->category = ($slug === '' ? null : $slug);
        $this->page = 1;
        $this->cached = null;
    }

    #[LiveAction]
    public function resetPage(): void
    {
        $this->page = 1;
        $this->cached = null;
    }

    #[LiveAction]
    public function goToPage(#[LiveArg] int $page): void
    {
        $this->page = max(1, $page);
        $this->cached = null;
    }
}
