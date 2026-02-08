<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260208090659 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove legacy DC2Type column comments no longer needed by Doctrine DBAL 4';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('COMMENT ON COLUMN category_media.media IS \'\'');
        $this->addSql('COMMENT ON COLUMN post_media.media IS \'\'');
        $this->addSql('COMMENT ON COLUMN post_section_media.media IS \'\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('COMMENT ON COLUMN category_media.media IS \'(DC2Type:media)\'');
        $this->addSql('COMMENT ON COLUMN post_media.media IS \'(DC2Type:media)\'');
        $this->addSql('COMMENT ON COLUMN post_section_media.media IS \'(DC2Type:media)\'');
    }
}
