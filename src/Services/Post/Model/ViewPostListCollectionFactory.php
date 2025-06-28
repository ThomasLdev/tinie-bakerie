<?php

namespace App\Services\Post\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\SerializerInterface;

readonly class ViewPostListCollectionFactory
{
    public function __construct(
        private SerializerInterface $serializer,
    )
    {
    }

    /**
     * @param array<array-key, mixed> $data
     */
    public function create(array $data): ArrayCollection
    {
        $collection = new ArrayCollection();

        foreach ($data as $item) {
            $viewPostList = $this->serializer->denormalize($item, ViewPostList::class);

            if (!$viewPostList instanceof ViewPostList) {
                continue;
            }

            $collection->add($viewPostList);
        }

        return $collection;
    }
}
