<?php

namespace App\Services\Post\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

readonly class ViewPostListCollectionFactory
{
    public function __construct(
        private DenormalizerInterface $denormalizer,
    ) {
    }

    /**
     * @param array<array-key, mixed> $data
     *
     * @return ArrayCollection<array-key, ViewPostList>
     *
     * @throws ExceptionInterface
     */
    public function create(array $data): ArrayCollection
    {
        $collection = new ArrayCollection();

        foreach ($data as $item) {
            $viewPostList = $this->denormalizer->denormalize($item, ViewPostList::class);

            if (!$viewPostList instanceof ViewPostList) {
                continue;
            }

            $collection->add($viewPostList);
        }

        return $collection;
    }
}
