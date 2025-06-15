<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250609095108 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE post_media_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE post_media_translation_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE post_media (id INT NOT NULL, post_id INT NOT NULL, media_name VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_FD372DE34B89032C ON post_media (post_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE post_media_translation (id INT NOT NULL, post_media_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, locale VARCHAR(2) DEFAULT 'en' NOT NULL, alt VARCHAR(255) DEFAULT '' NOT NULL, title VARCHAR(255) DEFAULT '' NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_754159D5557254D4 ON post_media_translation (post_media_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post_media ADD CONSTRAINT FK_FD372DE34B89032C FOREIGN KEY (post_id) REFERENCES post (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post_media_translation ADD CONSTRAINT FK_754159D5557254D4 FOREIGN KEY (post_media_id) REFERENCES post_media (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE category_translation ALTER locale SET DEFAULT 'en'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE category_translation ALTER locale TYPE VARCHAR(2)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post DROP image_name
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post_tag_translation ALTER locale SET DEFAULT 'en'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post_tag_translation ALTER locale TYPE VARCHAR(2)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post_translation ALTER locale SET DEFAULT 'en'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post_translation ALTER locale TYPE VARCHAR(2)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE post_media_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE post_media_translation_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post_media DROP CONSTRAINT FK_FD372DE34B89032C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post_media_translation DROP CONSTRAINT FK_754159D5557254D4
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE post_media
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE post_media_translation
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post_tag_translation ALTER locale DROP DEFAULT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post_tag_translation ALTER locale TYPE VARCHAR(255)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE category_translation ALTER locale DROP DEFAULT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE category_translation ALTER locale TYPE VARCHAR(255)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post_translation ALTER locale DROP DEFAULT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post_translation ALTER locale TYPE VARCHAR(255)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post ADD image_name VARCHAR(255) DEFAULT NULL
        SQL);
    }
}
