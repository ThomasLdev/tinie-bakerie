<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260117140238 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category_media ADD media VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE category_media DROP media_path');
        $this->addSql('ALTER TABLE category_media DROP type');
        $this->addSql('COMMENT ON COLUMN category_media.media IS \'(DC2Type:media)\'');
        $this->addSql('ALTER TABLE post_media ADD media VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE post_media DROP media_path');
        $this->addSql('ALTER TABLE post_media DROP type');
        $this->addSql('COMMENT ON COLUMN post_media.media IS \'(DC2Type:media)\'');
        $this->addSql('ALTER TABLE post_section_media ADD media VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE post_section_media DROP media_path');
        $this->addSql('ALTER TABLE post_section_media DROP type');
        $this->addSql('COMMENT ON COLUMN post_section_media.media IS \'(DC2Type:media)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE post_media ADD media_path VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE post_media ADD type VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE post_media DROP media');
        $this->addSql('ALTER TABLE category_media ADD media_path VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE category_media ADD type VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE category_media DROP media');
        $this->addSql('ALTER TABLE post_section_media ADD media_path VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE post_section_media ADD type VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE post_section_media DROP media');
    }
}
