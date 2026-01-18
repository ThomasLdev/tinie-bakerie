<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Enable pg_trgm extension for fuzzy text matching.
 */
final class Version20260117173203 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Enable pg_trgm extension for fuzzy text matching';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE EXTENSION IF NOT EXISTS pg_trgm');

        // Add trigram index on post_translation.title for fuzzy matching fallback
        $this->addSql(<<<'SQL'
            CREATE INDEX post_translation_title_trgm_idx
            ON post_translation
            USING GIN (title gin_trgm_ops)
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IF EXISTS post_translation_title_trgm_idx');
        $this->addSql('DROP EXTENSION IF EXISTS pg_trgm');
    }
}
