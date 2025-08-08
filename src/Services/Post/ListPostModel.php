<?php

namespace App\Services\Post;

readonly class ListPostModel
{
    public function __construct(
        public string $title,
        public ?\DateTimeImmutable $publishedAt,
        public \DateTime $createdAt,
        public \DateTime $updatedAt,
    )
    {
    }
}
