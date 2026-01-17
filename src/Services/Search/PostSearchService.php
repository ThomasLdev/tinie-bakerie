<?php

declare(strict_types=1);

namespace App\Services\Search;

use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;

final readonly class PostSearchService
{
    private const float WEIGHT_POST_TITLE = 4.0;

    private const float WEIGHT_POST_EXCERPT = 2.0;

    private const float WEIGHT_SECTION_CONTENT = 1.0;

    private const float WEIGHT_CATEGORY_TAG = 0.5;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private SearchQuerySanitizer $sanitizer,
    ) {
    }

    /**
     * Search posts by title, excerpt, section content, category, and tags.
     *
     * @return PostSearchResult[]
     */
    public function search(string $query, string $locale, int $limit = 5): array
    {
        $tsQuery = $this->sanitizer->toTsQuery($query);

        if ($tsQuery === '') {
            return [];
        }

        $sql = <<<'SQL'
                WITH search_results AS (
                    SELECT DISTINCT
                        p.id,
                        (
                            -- Post title weight (4.0x)
                            COALESCE(ts_rank(
                                to_tsvector('simple', COALESCE(pt.title, '')),
                                to_tsquery('simple', :query)
                            ), 0) * :weight_title
                            +
                            -- Post excerpt weight (2.0x)
                            COALESCE(ts_rank(
                                to_tsvector('simple', COALESCE(pt.excerpt, '')),
                                to_tsquery('simple', :query)
                            ), 0) * :weight_excerpt
                            +
                            -- Section content weight (1.0x) - aggregate from all sections
                            COALESCE((
                                SELECT SUM(ts_rank(
                                    to_tsvector('simple', COALESCE(pst.title, '') || ' ' || COALESCE(pst.content, '')),
                                    to_tsquery('simple', :query)
                                ))
                                FROM post_section ps
                                JOIN post_section_translation pst ON ps.id = pst.translatable_id
                                WHERE ps.post_id = p.id AND pst.locale = :locale
                            ), 0) * :weight_section
                            +
                            -- Category weight (0.5x)
                            COALESCE(ts_rank(
                                to_tsvector('simple', COALESCE(ct.title, '')),
                                to_tsquery('simple', :query)
                            ), 0) * :weight_category
                            +
                            -- Tags weight (0.5x) - aggregate from all tags
                            COALESCE((
                                SELECT SUM(ts_rank(
                                    to_tsvector('simple', COALESCE(tt.title, '')),
                                    to_tsquery('simple', :query)
                                ))
                                FROM post_tag ptag
                                JOIN tag_translation tt ON ptag.tag_id = tt.translatable_id
                                WHERE ptag.post_id = p.id AND tt.locale = :locale
                            ), 0) * :weight_category
                        ) AS rank
                    FROM post p
                    JOIN post_translation pt ON p.id = pt.translatable_id AND pt.locale = :locale
                    LEFT JOIN category c ON p.category_id = c.id
                    LEFT JOIN category_translation ct ON c.id = ct.translatable_id AND ct.locale = :locale
                    WHERE p.active = true
                    AND (
                        to_tsvector('simple', COALESCE(pt.title, '') || ' ' || COALESCE(pt.excerpt, ''))
                        @@ to_tsquery('simple', :query)
                        OR EXISTS (
                            SELECT 1
                            FROM post_section ps
                            JOIN post_section_translation pst ON ps.id = pst.translatable_id
                            WHERE ps.post_id = p.id
                            AND pst.locale = :locale
                            AND to_tsvector('simple', COALESCE(pst.title, '') || ' ' || COALESCE(pst.content, ''))
                                @@ to_tsquery('simple', :query)
                        )
                        OR to_tsvector('simple', COALESCE(ct.title, ''))
                            @@ to_tsquery('simple', :query)
                        OR EXISTS (
                            SELECT 1
                            FROM post_tag ptag
                            JOIN tag_translation tt ON ptag.tag_id = tt.translatable_id
                            WHERE ptag.post_id = p.id
                            AND tt.locale = :locale
                            AND to_tsvector('simple', COALESCE(tt.title, ''))
                                @@ to_tsquery('simple', :query)
                        )
                    )
                )
                SELECT id, rank
                FROM search_results
                WHERE rank > 0
                ORDER BY rank DESC
                LIMIT :limit
            SQL;

        $connection = $this->entityManager->getConnection();
        $results = $connection->executeQuery($sql, [
            'query' => $tsQuery,
            'locale' => $locale,
            'limit' => $limit,
            'weight_title' => self::WEIGHT_POST_TITLE,
            'weight_excerpt' => self::WEIGHT_POST_EXCERPT,
            'weight_section' => self::WEIGHT_SECTION_CONTENT,
            'weight_category' => self::WEIGHT_CATEGORY_TAG,
        ])->fetchAllAssociative();

        if ($results === []) {
            return [];
        }

        /** @var list<int> $postIds */
        $postIds = array_map(
            static fn (array $row): int => is_numeric($row['id']) ? (int) $row['id'] : 0,
            $results,
        );

        /** @var array<int, float> $ranks */
        $ranks = [];

        foreach ($results as $row) {
            $id = is_numeric($row['id']) ? (int) $row['id'] : 0;
            $rank = is_numeric($row['rank']) ? (float) $row['rank'] : 0.0;
            $ranks[$id] = $rank;
        }

        // Fetch Post entities with eager loading
        /** @var Post[] $posts */
        $posts = $this->entityManager
            ->getRepository(Post::class)
            ->createQueryBuilder('p')
            ->leftJoin('p.translations', 't')->addSelect('t')
            ->leftJoin('p.category', 'c')->addSelect('c')
            ->leftJoin('c.translations', 'ct')->addSelect('ct')
            ->leftJoin('p.media', 'm')->addSelect('m')
            ->leftJoin('m.translations', 'mt')->addSelect('mt')
            ->where('p.id IN (:ids)')
            ->setParameter('ids', $postIds)
            ->getQuery()
            ->getResult();

        // Map posts by id for ordering
        /** @var array<int, Post> $postsById */
        $postsById = [];

        foreach ($posts as $post) {
            $postsById[$post->getId()] = $post;
        }

        // Build results in ranked order
        $searchResults = [];

        foreach ($postIds as $id) {
            if (isset($postsById[$id])) {
                $searchResults[] = new PostSearchResult(
                    $postsById[$id],
                    $ranks[$id],
                );
            }
        }

        return $searchResults;
    }
}
