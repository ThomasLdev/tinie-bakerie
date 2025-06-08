<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250608143033 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE category_translation RENAME COLUMN locale_code TO locale
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_3F20704989D9B624180C698 ON category_translation (slug, locale)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post_tag_translation RENAME COLUMN locale_code TO locale
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post_translation RENAME COLUMN locale_code TO locale
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post_tag_translation RENAME COLUMN locale TO locale_code
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_3F20704989D9B624180C698
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE category_translation RENAME COLUMN locale TO locale_code
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post_translation RENAME COLUMN locale TO locale_code
        SQL);
    }
}
