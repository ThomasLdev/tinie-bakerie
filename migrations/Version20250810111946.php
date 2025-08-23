<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250810111946 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE post_section_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE post_section_media_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE post_section (id INT NOT NULL, post_id INT NOT NULL, media_id INT DEFAULT NULL, position INT DEFAULT 0 NOT NULL, type VARCHAR(255) DEFAULT \'default\' NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_109BCDDC4B89032C ON post_section (post_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_109BCDDCEA9FDD75 ON post_section (media_id)');
        $this->addSql('CREATE TABLE post_section_media (id INT NOT NULL, post_section_id INT DEFAULT NULL, media_name VARCHAR(255) DEFAULT NULL, type VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, alt VARCHAR(255) DEFAULT \'\' NOT NULL, title VARCHAR(255) DEFAULT \'\' NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E20A787BFB5BEABB ON post_section_media (post_section_id)');
        $this->addSql('ALTER TABLE post_section ADD CONSTRAINT FK_109BCDDC4B89032C FOREIGN KEY (post_id) REFERENCES post (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE post_section ADD CONSTRAINT FK_109BCDDCEA9FDD75 FOREIGN KEY (media_id) REFERENCES post_section_media (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE post_section_media ADD CONSTRAINT FK_E20A787BFB5BEABB FOREIGN KEY (post_section_id) REFERENCES post_section (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE post_section_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE post_section_media_id_seq CASCADE');
        $this->addSql('ALTER TABLE post_section DROP CONSTRAINT FK_109BCDDC4B89032C');
        $this->addSql('ALTER TABLE post_section DROP CONSTRAINT FK_109BCDDCEA9FDD75');
        $this->addSql('ALTER TABLE post_section_media DROP CONSTRAINT FK_E20A787BFB5BEABB');
        $this->addSql('DROP TABLE post_section');
        $this->addSql('DROP TABLE post_section_media');
    }
}
