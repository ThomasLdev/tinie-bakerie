<?php

declare(strict_types=1);

namespace App\Repository;

use App\Services\Locale\LocaleProvider;
use Doctrine\DBAL\Connection;

/**
 * PostgreSQL Full-Text Search implementation with:
 * - CTE optimization (tsquery computed once)
 * - Weighted ranking using setweight()
 * - ts_headline() for match highlighting
 * - pg_trgm fallback for fuzzy matching.
 */
final readonly class PostSearchRepository implements PostSearchRepositoryInterface
{
    public function __construct(
        private Connection $connection,
        private LocaleProvider $localeProvider,
    ) {
    }

    public function search(string $tsQuery, int $limit): array
    {
        $sql = <<<'SQL'
            WITH
            -- Compute tsquery once, reuse everywhere
            query AS (
                SELECT to_tsquery('simple', :query) AS q
            ),

            -- Aggregate section content per post
            section_vectors AS (
                SELECT
                    ps.post_id,
                    string_agg(COALESCE(pst.title, '') || ' ' || COALESCE(pst.content, ''), ' ') AS combined_text
                FROM post_section ps
                JOIN post_section_translation pst ON ps.id = pst.translatable_id AND pst.locale = :locale
                GROUP BY ps.post_id
            ),

            -- Aggregate tag titles per post
            tag_vectors AS (
                SELECT
                    pt.post_id,
                    string_agg(COALESCE(tt.title, ''), ' ') AS combined_text
                FROM post_tag pt
                JOIN tag_translation tt ON pt.tag_id = tt.translatable_id AND tt.locale = :locale
                GROUP BY pt.post_id
            ),

            -- Build weighted document vectors and compute ranking
            ranked_posts AS (
                SELECT
                    p.id,
                    ptr.title,
                    ptr.slug,
                    ptr.excerpt,
                    ctr.title AS category_title,
                    ctr.slug AS category_slug,
                    (
                        SELECT pm.media
                        FROM post_media pm
                        WHERE pm.post_id = p.id
                        ORDER BY pm.position ASC
                        LIMIT 1
                    ) AS image_path,

                    -- Combined weighted tsvector for the entire document
                    (
                        setweight(to_tsvector('simple', COALESCE(ptr.title, '')), 'A') ||
                        setweight(to_tsvector('simple', COALESCE(ptr.excerpt, '')), 'B') ||
                        setweight(to_tsvector('simple', COALESCE(sv.combined_text, '')), 'C') ||
                        setweight(to_tsvector('simple', COALESCE(ctr.title, '')), 'D') ||
                        setweight(to_tsvector('simple', COALESCE(tv.combined_text, '')), 'D')
                    ) AS document_vector,

                    -- Primary text for headline generation (title + excerpt)
                    COALESCE(ptr.title, '') || ' ' || COALESCE(ptr.excerpt, '') AS headline_source

                FROM post p
                JOIN post_translation ptr ON p.id = ptr.translatable_id AND ptr.locale = :locale
                LEFT JOIN category c ON p.category_id = c.id
                LEFT JOIN category_translation ctr ON c.id = ctr.translatable_id AND ctr.locale = :locale
                LEFT JOIN section_vectors sv ON sv.post_id = p.id
                LEFT JOIN tag_vectors tv ON tv.post_id = p.id
                WHERE p.active = true
            ),

            -- FTS results with ranking
            fts_results AS (
                SELECT
                    rp.id,
                    rp.title,
                    rp.slug,
                    rp.excerpt,
                    rp.category_title,
                    rp.category_slug,
                    rp.image_path,
                    ts_rank_cd(rp.document_vector, q.q, 32) AS rank,
                    ts_headline(
                        'simple',
                        rp.headline_source,
                        q.q,
                        'StartSel=<b>, StopSel=</b>, MaxWords=35, MinWords=15, MaxFragments=1'
                    ) AS headline
                FROM ranked_posts rp
                CROSS JOIN query q
                WHERE rp.document_vector @@ q.q
            ),

            -- Trigram fallback for fuzzy matching (when FTS has no results)
            trigram_results AS (
                SELECT
                    rp.id,
                    rp.title,
                    rp.slug,
                    rp.excerpt,
                    rp.category_title,
                    rp.category_slug,
                    rp.image_path,
                    similarity(rp.title, :raw_query) AS rank,
                    NULL::text AS headline
                FROM ranked_posts rp
                WHERE NOT EXISTS (SELECT 1 FROM fts_results)
                  AND similarity(rp.title, :raw_query) > 0.1
            ),

            -- Combine FTS and trigram results
            combined_results AS (
                SELECT * FROM fts_results
                UNION ALL
                SELECT * FROM trigram_results
            )

            SELECT
                id,
                title,
                slug,
                excerpt,
                category_title,
                category_slug,
                image_path,
                rank,
                headline
            FROM combined_results
            WHERE rank > 0
            ORDER BY rank DESC
            LIMIT :limit
            SQL;

        /** @var list<array{id: int, title: string, slug: string, excerpt: string, category_title: string|null, category_slug: string|null, image_path: string|null, rank: float, headline: string|null}> */
        return $this->connection->executeQuery($sql, [
            'query' => $tsQuery,
            'raw_query' => $this->extractRawQuery($tsQuery),
            'locale' => $this->localeProvider->getCurrentLocale(),
            'limit' => $limit,
        ])->fetchAllAssociative();
    }

    /**
     * Extract the raw search terms from the tsquery format for trigram matching.
     * Converts "choco:* & cake:*" back to "choco cake".
     */
    private function extractRawQuery(string $tsQuery): string
    {
        // Remove :* prefix markers and & operators
        $raw = preg_replace('/:\*/', '', $tsQuery) ?? $tsQuery;
        $raw = preg_replace('/\s*&\s*/', ' ', $raw) ?? $raw;

        return trim($raw);
    }
}
