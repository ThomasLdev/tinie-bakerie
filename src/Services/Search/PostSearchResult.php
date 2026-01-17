<?php

declare(strict_types=1);

namespace App\Services\Search;

use App\Entity\Post;

final readonly class PostSearchResult
{
    public function __construct(
        public Post $post,
        public float $rank,
    ) {
    }
}
