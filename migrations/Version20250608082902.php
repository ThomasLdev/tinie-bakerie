<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250608082902 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE category (id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE category_translation (id INT NOT NULL, category_id INT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT '' NOT NULL, slug VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, locale_code VARCHAR(255) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3F2070412469DE2 ON category_translation (category_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE post (id INT NOT NULL, category_id INT DEFAULT NULL, image_name VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_5A8A6C8D12469DE2 ON post (category_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE post_post_tag (post_id INT NOT NULL, post_tag_id INT NOT NULL, PRIMARY KEY(post_id, post_tag_id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E523B3514B89032C ON post_post_tag (post_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E523B3518AF08774 ON post_post_tag (post_tag_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE post_tag (id INT NOT NULL, color VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE post_tag_translation (id INT NOT NULL, post_tag_id INT NOT NULL, name VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, locale_code VARCHAR(255) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E596FFE78AF08774 ON post_tag_translation (post_tag_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE post_translation (id INT NOT NULL, post_id INT NOT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, locale_code VARCHAR(255) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_5829CF404B89032C ON post_translation (post_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE post_translation_section (id INT NOT NULL, translation_id INT NOT NULL, media_id INT DEFAULT NULL, text TEXT DEFAULT NULL, position INT NOT NULL, type VARCHAR(255) DEFAULT 'text_plain' NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_623366E99CAA2B25 ON post_translation_section (translation_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_623366E9EA9FDD75 ON post_translation_section (media_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE post_translation_section_media (id INT NOT NULL, media_name VARCHAR(255) DEFAULT NULL, media_type VARCHAR(255) DEFAULT 'image' NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE post_translation_section_media_translation (id INT NOT NULL, media_id INT NOT NULL, alt VARCHAR(255) DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_D8BE5245EA9FDD75 ON post_translation_section_media_translation (media_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE "user" (id INT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON "user" (email)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE category_translation ADD CONSTRAINT FK_3F2070412469DE2 FOREIGN KEY (category_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8D12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post_post_tag ADD CONSTRAINT FK_E523B3514B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post_post_tag ADD CONSTRAINT FK_E523B3518AF08774 FOREIGN KEY (post_tag_id) REFERENCES post_tag (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post_tag_translation ADD CONSTRAINT FK_E596FFE78AF08774 FOREIGN KEY (post_tag_id) REFERENCES post_tag (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post_translation ADD CONSTRAINT FK_5829CF404B89032C FOREIGN KEY (post_id) REFERENCES post (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post_translation_section ADD CONSTRAINT FK_623366E99CAA2B25 FOREIGN KEY (translation_id) REFERENCES post_translation (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post_translation_section ADD CONSTRAINT FK_623366E9EA9FDD75 FOREIGN KEY (media_id) REFERENCES post_translation_section_media (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post_translation_section_media_translation ADD CONSTRAINT FK_D8BE5245EA9FDD75 FOREIGN KEY (media_id) REFERENCES post_translation_section_media (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE category_translation DROP CONSTRAINT FK_3F2070412469DE2
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post DROP CONSTRAINT FK_5A8A6C8D12469DE2
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post_post_tag DROP CONSTRAINT FK_E523B3514B89032C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post_post_tag DROP CONSTRAINT FK_E523B3518AF08774
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post_tag_translation DROP CONSTRAINT FK_E596FFE78AF08774
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post_translation DROP CONSTRAINT FK_5829CF404B89032C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post_translation_section DROP CONSTRAINT FK_623366E99CAA2B25
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post_translation_section DROP CONSTRAINT FK_623366E9EA9FDD75
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post_translation_section_media_translation DROP CONSTRAINT FK_D8BE5245EA9FDD75
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE category
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE category_translation
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE post
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE post_post_tag
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE post_tag
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE post_tag_translation
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE post_translation
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE post_translation_section
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE post_translation_section_media
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE post_translation_section_media_translation
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE "user"
        SQL);
    }
}
