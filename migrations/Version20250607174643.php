<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250607174643 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE media_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE media_translation_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE media (id INT NOT NULL, path VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE media_translation (id INT NOT NULL, media_id INT NOT NULL, locale_code VARCHAR(255) NOT NULL, alt VARCHAR(255) DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_430137FCEA9FDD75 ON media_translation (media_id)');
        $this->addSql('ALTER TABLE media_translation ADD CONSTRAINT FK_430137FCEA9FDD75 FOREIGN KEY (media_id) REFERENCES media (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE post_translation_section ADD media_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE post_translation_section DROP media');
        $this->addSql('ALTER TABLE post_translation_section ADD CONSTRAINT FK_623366E9EA9FDD75 FOREIGN KEY (media_id) REFERENCES media (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_623366E9EA9FDD75 ON post_translation_section (media_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE post_translation_section DROP CONSTRAINT FK_623366E9EA9FDD75');
        $this->addSql('DROP SEQUENCE media_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE media_translation_id_seq CASCADE');
        $this->addSql('ALTER TABLE media_translation DROP CONSTRAINT FK_430137FCEA9FDD75');
        $this->addSql('DROP TABLE media');
        $this->addSql('DROP TABLE media_translation');
        $this->addSql('DROP INDEX UNIQ_623366E9EA9FDD75');
        $this->addSql('ALTER TABLE post_translation_section ADD media VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE post_translation_section DROP media_id');
    }
}
