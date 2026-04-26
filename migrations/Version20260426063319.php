<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260426063319 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tag ADD image VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE tag ADD is_featured BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE tag DROP background_color');
        $this->addSql('ALTER TABLE tag DROP text_color');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tag ADD background_color VARCHAR(255) DEFAULT \'#000000\' NOT NULL');
        $this->addSql('ALTER TABLE tag ADD text_color VARCHAR(255) DEFAULT \'#FFFFFF\' NOT NULL');
        $this->addSql('ALTER TABLE tag DROP image');
        $this->addSql('ALTER TABLE tag DROP is_featured');
    }
}
