<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250615053416 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP SEQUENCE post_translation_section_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE post_translation_section_media_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE post_translation_section_media_translation_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE category_media_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE category_media_translation_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE post_section_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE post_section_media_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE post_section_media_translation_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE post_section_translation_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE category_media (id INT NOT NULL, category_id INT NOT NULL, media_name VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_821FEE4512469DE2 ON category_media (category_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE category_media_translation (id INT NOT NULL, category_media_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, locale VARCHAR(2) DEFAULT 'en' NOT NULL, alt VARCHAR(255) DEFAULT '' NOT NULL, title VARCHAR(255) DEFAULT '' NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_A9514D7C5DE5590E ON category_media_translation (category_media_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE post_section (id INT NOT NULL, post_id INT NOT NULL, media_id INT DEFAULT NULL, text TEXT DEFAULT '' NOT NULL, position INT DEFAULT 0 NOT NULL, type VARCHAR(255) DEFAULT 'text_plain' NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_109BCDDC4B89032C ON post_section (post_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_109BCDDCEA9FDD75 ON post_section (media_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE post_section_media (id INT NOT NULL, media_name VARCHAR(255) DEFAULT NULL, media_type VARCHAR(255) DEFAULT 'image' NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE post_section_media_translation (id INT NOT NULL, media_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, alt VARCHAR(255) DEFAULT '' NOT NULL, title VARCHAR(255) DEFAULT '' NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_F80F5879EA9FDD75 ON post_section_media_translation (media_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE post_section_translation (id INT NOT NULL, post_section_id INT NOT NULL, content TEXT DEFAULT '' NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, locale VARCHAR(2) DEFAULT 'en' NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_4CD4DFE4FB5BEABB ON post_section_translation (post_section_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE category_media ADD CONSTRAINT FK_821FEE4512469DE2 FOREIGN KEY (category_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE category_media_translation ADD CONSTRAINT FK_A9514D7C5DE5590E FOREIGN KEY (category_media_id) REFERENCES category_media (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post_section ADD CONSTRAINT FK_109BCDDC4B89032C FOREIGN KEY (post_id) REFERENCES post (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post_section ADD CONSTRAINT FK_109BCDDCEA9FDD75 FOREIGN KEY (media_id) REFERENCES post_section_media (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post_section_media_translation ADD CONSTRAINT FK_F80F5879EA9FDD75 FOREIGN KEY (media_id) REFERENCES post_section_media (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post_section_translation ADD CONSTRAINT FK_4CD4DFE4FB5BEABB FOREIGN KEY (post_section_id) REFERENCES post_section (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post_translation_section_media_translation DROP CONSTRAINT fk_d8be5245ea9fdd75
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post_translation_section DROP CONSTRAINT fk_623366e99caa2b25
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post_translation_section DROP CONSTRAINT fk_623366e9ea9fdd75
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE post_translation_section_media_translation
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE post_translation_section_media
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE post_translation_section
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE category_translation ALTER name SET DEFAULT ''
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post_translation ALTER title SET DEFAULT ''
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE category_media_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE category_media_translation_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE post_section_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE post_section_media_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE post_section_media_translation_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE post_section_translation_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE post_translation_section_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE post_translation_section_media_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE post_translation_section_media_translation_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE post_translation_section_media_translation (id INT NOT NULL, media_id INT NOT NULL, alt VARCHAR(255) DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_d8be5245ea9fdd75 ON post_translation_section_media_translation (media_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE post_translation_section_media (id INT NOT NULL, media_name VARCHAR(255) DEFAULT NULL, media_type VARCHAR(255) DEFAULT 'image' NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE post_translation_section (id INT NOT NULL, translation_id INT NOT NULL, media_id INT DEFAULT NULL, text TEXT DEFAULT NULL, "position" INT NOT NULL, type VARCHAR(255) DEFAULT 'text_plain' NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX uniq_623366e9ea9fdd75 ON post_translation_section (media_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_623366e99caa2b25 ON post_translation_section (translation_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post_translation_section_media_translation ADD CONSTRAINT fk_d8be5245ea9fdd75 FOREIGN KEY (media_id) REFERENCES post_translation_section_media (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post_translation_section ADD CONSTRAINT fk_623366e99caa2b25 FOREIGN KEY (translation_id) REFERENCES post_translation (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post_translation_section ADD CONSTRAINT fk_623366e9ea9fdd75 FOREIGN KEY (media_id) REFERENCES post_translation_section_media (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE category_media DROP CONSTRAINT FK_821FEE4512469DE2
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE category_media_translation DROP CONSTRAINT FK_A9514D7C5DE5590E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post_section DROP CONSTRAINT FK_109BCDDC4B89032C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post_section DROP CONSTRAINT FK_109BCDDCEA9FDD75
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post_section_media_translation DROP CONSTRAINT FK_F80F5879EA9FDD75
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post_section_translation DROP CONSTRAINT FK_4CD4DFE4FB5BEABB
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE category_media
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE category_media_translation
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE post_section
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE post_section_media
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE post_section_media_translation
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE post_section_translation
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE category_translation ALTER name DROP DEFAULT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post_translation ALTER title DROP DEFAULT
        SQL);
    }
}
