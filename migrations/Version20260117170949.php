<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add GIN indexes for PostgreSQL Full-Text Search on translation tables.
 */
final class Version20260117170949 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add GIN indexes for PostgreSQL Full-Text Search on translation tables';
    }

    public function up(Schema $schema): void
    {
        // GIN index on post_translation (title, excerpt)
        $this->addSql(<<<'SQL'
            CREATE INDEX post_translation_fts_idx
            ON post_translation
            USING GIN (to_tsvector('simple', coalesce(title, '') || ' ' || coalesce(excerpt, '')))
        SQL);

        // GIN index on post_section_translation (title, content)
        $this->addSql(<<<'SQL'
            CREATE INDEX post_section_translation_fts_idx
            ON post_section_translation
            USING GIN (to_tsvector('simple', coalesce(title, '') || ' ' || coalesce(content, '')))
        SQL);

        // GIN index on category_translation (title)
        $this->addSql(<<<'SQL'
            CREATE INDEX category_translation_fts_idx
            ON category_translation
            USING GIN (to_tsvector('simple', coalesce(title, '')))
        SQL);

        // GIN index on tag_translation (title)
        $this->addSql(<<<'SQL'
            CREATE INDEX tag_translation_fts_idx
            ON tag_translation
            USING GIN (to_tsvector('simple', coalesce(title, '')))
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IF EXISTS post_translation_fts_idx');
        $this->addSql('DROP INDEX IF EXISTS post_section_translation_fts_idx');
        $this->addSql('DROP INDEX IF EXISTS category_translation_fts_idx');
        $this->addSql('DROP INDEX IF EXISTS tag_translation_fts_idx');
    }
}
