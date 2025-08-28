<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250828211348 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE post ADD active BOOLEAN DEFAULT true NOT NULL');
        $this->addSql('ALTER TABLE post DROP published_at');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE post ADD published_at DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE post DROP active');
        $this->addSql('COMMENT ON COLUMN post.published_at IS \'(DC2Type:date_immutable)\'');
    }
}
