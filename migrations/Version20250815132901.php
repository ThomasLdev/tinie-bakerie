<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250815132901 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE post_section DROP CONSTRAINT fk_109bcddcea9fdd75');
        $this->addSql('DROP INDEX uniq_109bcddcea9fdd75');
        $this->addSql('ALTER TABLE post_section DROP media_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE post_section ADD media_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE post_section ADD CONSTRAINT fk_109bcddcea9fdd75 FOREIGN KEY (media_id) REFERENCES post_section_media (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_109bcddcea9fdd75 ON post_section (media_id)');
    }
}
