<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250831103126 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE category (id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE category_media (id INT NOT NULL, category_id INT DEFAULT NULL, media_name VARCHAR(255) DEFAULT NULL, type VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_821FEE4512469DE2 ON category_media (category_id)');
        $this->addSql('CREATE TABLE category_media_translation (id INT NOT NULL, translatable_id INT DEFAULT NULL, locale VARCHAR(255) NOT NULL, alt VARCHAR(255) DEFAULT \'\' NOT NULL, title VARCHAR(255) DEFAULT \'\' NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A9514D7C2C2AC5D3 ON category_media_translation (translatable_id)');
        $this->addSql('CREATE TABLE category_translation (id INT NOT NULL, translatable_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT \'\' NOT NULL, meta_description TEXT DEFAULT \'\' NOT NULL, meta_title VARCHAR(60) DEFAULT \'\' NOT NULL, excerpt TEXT DEFAULT \'\' NOT NULL, locale VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3F207042C2AC5D3 ON category_translation (translatable_id)');
        $this->addSql('CREATE UNIQUE INDEX category_translation_unique_idx ON category_translation (locale, title)');
        $this->addSql('CREATE TABLE post (id INT NOT NULL, category_id INT DEFAULT NULL, reading_time INT DEFAULT 0 NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, active BOOLEAN DEFAULT true NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_5A8A6C8D12469DE2 ON post (category_id)');
        $this->addSql('CREATE TABLE post_tag (post_id INT NOT NULL, tag_id INT NOT NULL, PRIMARY KEY(post_id, tag_id))');
        $this->addSql('CREATE INDEX IDX_5ACE3AF04B89032C ON post_tag (post_id)');
        $this->addSql('CREATE INDEX IDX_5ACE3AF0BAD26311 ON post_tag (tag_id)');
        $this->addSql('CREATE TABLE post_media (id INT NOT NULL, post_id INT DEFAULT NULL, media_name VARCHAR(255) DEFAULT NULL, type VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_FD372DE34B89032C ON post_media (post_id)');
        $this->addSql('CREATE TABLE post_media_translation (id INT NOT NULL, translatable_id INT DEFAULT NULL, locale VARCHAR(255) NOT NULL, alt VARCHAR(255) DEFAULT \'\' NOT NULL, title VARCHAR(255) DEFAULT \'\' NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_754159D52C2AC5D3 ON post_media_translation (translatable_id)');
        $this->addSql('CREATE TABLE post_section (id INT NOT NULL, post_id INT DEFAULT NULL, position INT DEFAULT 0 NOT NULL, type VARCHAR(255) DEFAULT \'default\' NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_109BCDDC4B89032C ON post_section (post_id)');
        $this->addSql('CREATE TABLE post_section_media (id INT NOT NULL, post_section_id INT DEFAULT NULL, media_name VARCHAR(255) DEFAULT NULL, type VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E20A787BFB5BEABB ON post_section_media (post_section_id)');
        $this->addSql('CREATE TABLE post_section_media_translation (id INT NOT NULL, translatable_id INT DEFAULT NULL, locale VARCHAR(255) NOT NULL, alt VARCHAR(255) DEFAULT \'\' NOT NULL, title VARCHAR(255) DEFAULT \'\' NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F80F58792C2AC5D3 ON post_section_media_translation (translatable_id)');
        $this->addSql('CREATE TABLE post_section_translation (id INT NOT NULL, translatable_id INT DEFAULT NULL, content TEXT DEFAULT \'\' NOT NULL, locale VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_4CD4DFE42C2AC5D3 ON post_section_translation (translatable_id)');
        $this->addSql('CREATE TABLE post_translation (id INT NOT NULL, translatable_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, meta_description TEXT DEFAULT \'\' NOT NULL, meta_title VARCHAR(60) DEFAULT \'\' NOT NULL, excerpt TEXT DEFAULT \'\' NOT NULL, slug VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, locale VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_5829CF402C2AC5D3 ON post_translation (translatable_id)');
        $this->addSql('CREATE UNIQUE INDEX post_translation_lookup_unique_idx ON post_translation (locale, title)');
        $this->addSql('CREATE TABLE tag (id INT NOT NULL, color VARCHAR(255) DEFAULT \'#000000\' NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE tag_translation (id INT NOT NULL, translatable_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, locale VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A8A03F8F2C2AC5D3 ON tag_translation (translatable_id)');
        $this->addSql('CREATE UNIQUE INDEX tag_translation_unique_idx ON tag_translation (locale, title)');
        $this->addSql('CREATE TABLE "user" (id INT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON "user" (email)');
        $this->addSql('ALTER TABLE category_media ADD CONSTRAINT FK_821FEE4512469DE2 FOREIGN KEY (category_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE category_media_translation ADD CONSTRAINT FK_A9514D7C2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES category_media (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE category_translation ADD CONSTRAINT FK_3F207042C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES category (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8D12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE post_tag ADD CONSTRAINT FK_5ACE3AF04B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE post_tag ADD CONSTRAINT FK_5ACE3AF0BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE post_media ADD CONSTRAINT FK_FD372DE34B89032C FOREIGN KEY (post_id) REFERENCES post (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE post_media_translation ADD CONSTRAINT FK_754159D52C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES post_media (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE post_section ADD CONSTRAINT FK_109BCDDC4B89032C FOREIGN KEY (post_id) REFERENCES post (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE post_section_media ADD CONSTRAINT FK_E20A787BFB5BEABB FOREIGN KEY (post_section_id) REFERENCES post_section (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE post_section_media_translation ADD CONSTRAINT FK_F80F58792C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES post_section_media (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE post_section_translation ADD CONSTRAINT FK_4CD4DFE42C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES post_section (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE post_translation ADD CONSTRAINT FK_5829CF402C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES post (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tag_translation ADD CONSTRAINT FK_A8A03F8F2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES tag (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE category_media DROP CONSTRAINT FK_821FEE4512469DE2');
        $this->addSql('ALTER TABLE category_media_translation DROP CONSTRAINT FK_A9514D7C2C2AC5D3');
        $this->addSql('ALTER TABLE category_translation DROP CONSTRAINT FK_3F207042C2AC5D3');
        $this->addSql('ALTER TABLE post DROP CONSTRAINT FK_5A8A6C8D12469DE2');
        $this->addSql('ALTER TABLE post_tag DROP CONSTRAINT FK_5ACE3AF04B89032C');
        $this->addSql('ALTER TABLE post_tag DROP CONSTRAINT FK_5ACE3AF0BAD26311');
        $this->addSql('ALTER TABLE post_media DROP CONSTRAINT FK_FD372DE34B89032C');
        $this->addSql('ALTER TABLE post_media_translation DROP CONSTRAINT FK_754159D52C2AC5D3');
        $this->addSql('ALTER TABLE post_section DROP CONSTRAINT FK_109BCDDC4B89032C');
        $this->addSql('ALTER TABLE post_section_media DROP CONSTRAINT FK_E20A787BFB5BEABB');
        $this->addSql('ALTER TABLE post_section_media_translation DROP CONSTRAINT FK_F80F58792C2AC5D3');
        $this->addSql('ALTER TABLE post_section_translation DROP CONSTRAINT FK_4CD4DFE42C2AC5D3');
        $this->addSql('ALTER TABLE post_translation DROP CONSTRAINT FK_5829CF402C2AC5D3');
        $this->addSql('ALTER TABLE tag_translation DROP CONSTRAINT FK_A8A03F8F2C2AC5D3');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE category_media');
        $this->addSql('DROP TABLE category_media_translation');
        $this->addSql('DROP TABLE category_translation');
        $this->addSql('DROP TABLE post');
        $this->addSql('DROP TABLE post_tag');
        $this->addSql('DROP TABLE post_media');
        $this->addSql('DROP TABLE post_media_translation');
        $this->addSql('DROP TABLE post_section');
        $this->addSql('DROP TABLE post_section_media');
        $this->addSql('DROP TABLE post_section_media_translation');
        $this->addSql('DROP TABLE post_section_translation');
        $this->addSql('DROP TABLE post_translation');
        $this->addSql('DROP TABLE tag');
        $this->addSql('DROP TABLE tag_translation');
        $this->addSql('DROP TABLE "user"');
    }
}
