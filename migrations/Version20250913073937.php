<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250913073937 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category_media_translation ALTER translatable_id DROP NOT NULL');
        $this->addSql('ALTER TABLE category_translation ALTER translatable_id DROP NOT NULL');
        $this->addSql('ALTER TABLE post_media_translation ALTER translatable_id DROP NOT NULL');
        $this->addSql('ALTER TABLE post_section_media_translation ALTER translatable_id DROP NOT NULL');
        $this->addSql('ALTER TABLE post_section_translation ALTER translatable_id DROP NOT NULL');
        $this->addSql('ALTER TABLE post_translation ALTER translatable_id DROP NOT NULL');
        $this->addSql('ALTER TABLE tag_translation ALTER translatable_id DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE post_translation ALTER translatable_id SET NOT NULL');
        $this->addSql('ALTER TABLE category_media_translation ALTER translatable_id SET NOT NULL');
        $this->addSql('ALTER TABLE tag_translation ALTER translatable_id SET NOT NULL');
        $this->addSql('ALTER TABLE category_translation ALTER translatable_id SET NOT NULL');
        $this->addSql('ALTER TABLE post_section_translation ALTER translatable_id SET NOT NULL');
        $this->addSql('ALTER TABLE post_section_media_translation ALTER translatable_id SET NOT NULL');
        $this->addSql('ALTER TABLE post_media_translation ALTER translatable_id SET NOT NULL');
    }
}
