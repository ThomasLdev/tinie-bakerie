<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250810113349 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE post ADD meta_description TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE post ADD meta_title VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE post ADD keywords TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE post ADD excerpt TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE post ADD canonical_url VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE post ADD indexable BOOLEAN DEFAULT true NOT NULL');
        $this->addSql('ALTER TABLE post ADD followable BOOLEAN DEFAULT true NOT NULL');
        $this->addSql('ALTER TABLE post ADD og_title VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE post ADD og_description TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE post ADD og_image VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE post ADD twitter_card VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE post ADD structured_data_type VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE post DROP meta_description');
        $this->addSql('ALTER TABLE post DROP meta_title');
        $this->addSql('ALTER TABLE post DROP keywords');
        $this->addSql('ALTER TABLE post DROP excerpt');
        $this->addSql('ALTER TABLE post DROP canonical_url');
        $this->addSql('ALTER TABLE post DROP indexable');
        $this->addSql('ALTER TABLE post DROP followable');
        $this->addSql('ALTER TABLE post DROP og_title');
        $this->addSql('ALTER TABLE post DROP og_description');
        $this->addSql('ALTER TABLE post DROP og_image');
        $this->addSql('ALTER TABLE post DROP twitter_card');
        $this->addSql('ALTER TABLE post DROP structured_data_type');
    }
}
