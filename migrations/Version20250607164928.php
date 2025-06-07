<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250607164928 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE post_translation_section_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE post_translation_section (id INT NOT NULL, translation_id INT NOT NULL, media VARCHAR(255) DEFAULT NULL, text TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_623366E99CAA2B25 ON post_translation_section (translation_id)');
        $this->addSql('ALTER TABLE post_translation_section ADD CONSTRAINT FK_623366E99CAA2B25 FOREIGN KEY (translation_id) REFERENCES post_translation (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE category_translation ADD description VARCHAR(255) DEFAULT \'\' NOT NULL');
        $this->addSql('ALTER TABLE category_translation ALTER category_id SET NOT NULL');
        $this->addSql('ALTER TABLE post ADD thumbnail VARCHAR(255) DEFAULT \'\' NOT NULL');
        $this->addSql('ALTER TABLE post_translation DROP content');
        $this->addSql('ALTER TABLE post_translation ALTER post_id SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE post_translation_section_id_seq CASCADE');
        $this->addSql('ALTER TABLE post_translation_section DROP CONSTRAINT FK_623366E99CAA2B25');
        $this->addSql('DROP TABLE post_translation_section');
        $this->addSql('ALTER TABLE post DROP thumbnail');
        $this->addSql('ALTER TABLE category_translation DROP description');
        $this->addSql('ALTER TABLE category_translation ALTER category_id DROP NOT NULL');
        $this->addSql('ALTER TABLE post_translation ADD content TEXT DEFAULT \'\' NOT NULL');
        $this->addSql('ALTER TABLE post_translation ALTER post_id DROP NOT NULL');
    }
}
