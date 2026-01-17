<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\DBAL\Connection;

/**
 * PostgreSQL Full-Text Search implementation.
 * Returns all display data in a single query - no entity hydration needed.
 */
final readonly class PostSearchRepository implements PostSearchRepositoryInterface
{
    private const float WEIGHT_POST_TITLE = 4.0;

    private const float WEIGHT_POST_EXCERPT = 2.0;

    private const float WEIGHT_SECTION_CONTENT = 1.0;

    private const float WEIGHT_CATEGORY_TAG = 0.5;

    public function __construct(
        private Connection $connection,
    ) {
    }

    public function search(string $tsQuery, string $locale, int $limit): array
    {
        $sql = <<<'SQL'
            WITH ranked_posts AS (
                SELECT DISTINCT
                    p.id,
                    pt.title,
                    pt.slug,
                    pt.excerpt,
                    ct.title AS category_title,
                    ct.slug AS category_slug,
                    (
                        SELECT pm.media
                        FROM post_media pm
                        WHERE pm.post_id = p.id
                        ORDER BY pm.position ASC
                        LIMIT 1
                    ) AS image_path,
                    (
                        COALESCE(ts_rank(
                            to_tsvector('simple', COALESCE(pt.title, '')),
                            to_tsquery('simple', :query)
                        ), 0) * :weight_title
                        +
                        COALESCE(ts_rank(
                            to_tsvector('simple', COALESCE(pt.excerpt, '')),
                            to_tsquery('simple', :query)
                        ), 0) * :weight_excerpt
                        +
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
                        COALESCE(ts_rank(
                            to_tsvector('simple', COALESCE(ct.title, '')),
                            to_tsquery('simple', :query)
                        ), 0) * :weight_category
                        +
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
            SELECT
                id,
                title,
                slug,
                excerpt,
                category_title,
                category_slug,
                image_path,
                rank
            FROM ranked_posts
            WHERE rank > 0
            ORDER BY rank DESC
            LIMIT :limit
            SQL;

        /** @var list<array{id: int, title: string, slug: string, excerpt: string, category_title: string|null, category_slug: string|null, image_path: string|null, rank: float}> $results */
        $results = $this->connection->executeQuery($sql, [
            'query' => $tsQuery,
            'locale' => $locale,
            'limit' => $limit,
            'weight_title' => self::WEIGHT_POST_TITLE,
            'weight_excerpt' => self::WEIGHT_POST_EXCERPT,
            'weight_section' => self::WEIGHT_SECTION_CONTENT,
            'weight_category' => self::WEIGHT_CATEGORY_TAG,
        ])->fetchAllAssociative();

        return $results;
    }
}
