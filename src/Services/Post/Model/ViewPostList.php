<?php

namespace App\Services\Post\Model;

use App\Services\EntityModelInterface;

class ViewPostList implements EntityModelInterface
{
    public function __construct(
        public ?int $id = null,
        public string $postTitle = '',
        public string $postSlug = '',
        public ?int $categoryId = null,
        public string $categorySlug = '',
    ) {
    }
}
