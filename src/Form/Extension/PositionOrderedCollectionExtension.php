<?php

declare(strict_types=1);

namespace App\Form\Extension;

use App\Entity\Contracts\Positionable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Sorts CollectionType data by `getPosition()` so collections always render in
 * position order regardless of how the underlying Doctrine collection was
 * hydrated or mutated in memory.
 */
final class PositionOrderedCollectionExtension extends AbstractTypeExtension
{
    #[\Override]
    public static function getExtendedTypes(): iterable
    {
        return [CollectionType::class];
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // by_reference=true means Symfony mutates the original collection in
        // place; replacing it with a sorted copy would defeat that contract.
        if ($options['by_reference'] ?? true) {
            return;
        }

        $builder->addEventListener(FormEvents::PRE_SET_DATA, $this->sortByPosition(...));
    }

    private function sortByPosition(FormEvent $event): void
    {
        $data = $event->getData();

        if (null === $data) {
            return;
        }

        $items = match (true) {
            $data instanceof Collection => $data->toArray(),
            \is_array($data) => $data,
            default => null,
        };

        if (null === $items || [] === $items) {
            return;
        }

        $positionables = [];

        foreach ($items as $item) {
            if (!$item instanceof Positionable) {
                return;
            }
            $positionables[] = $item;
        }

        usort($positionables, static fn (Positionable $a, Positionable $b): int => $a->getPosition() <=> $b->getPosition());

        $event->setData($data instanceof Collection ? new ArrayCollection($positionables) : $positionables);
    }
}
