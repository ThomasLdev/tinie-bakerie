<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250928072946 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE post ADD cooking_time INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE post ADD difficulty VARCHAR(255) DEFAULT \'easy\' NOT NULL');
        $this->addSql('ALTER TABLE post_translation ADD notes TEXT DEFAULT \'\' NOT NULL');
        $this->addSql('ALTER TABLE tag ADD text_color VARCHAR(255) DEFAULT \'#FFFFFF\' NOT NULL');
        $this->addSql('ALTER TABLE tag RENAME COLUMN color TO background_color');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE post_translation DROP notes');
        $this->addSql('ALTER TABLE post DROP cooking_time');
        $this->addSql('ALTER TABLE post DROP difficulty');
        $this->addSql('ALTER TABLE tag DROP text_color');
        $this->addSql('ALTER TABLE tag RENAME COLUMN background_color TO color');
    }
}
