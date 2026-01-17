<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260117123550 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category_media RENAME COLUMN media_name TO media_path');
        $this->addSql('ALTER TABLE post_media RENAME COLUMN media_name TO media_path');
        $this->addSql('ALTER TABLE post_section_media RENAME COLUMN media_name TO media_path');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE post_media RENAME COLUMN media_path TO media_name');
        $this->addSql('ALTER TABLE category_media RENAME COLUMN media_path TO media_name');
        $this->addSql('ALTER TABLE post_section_media RENAME COLUMN media_path TO media_name');
    }
}
