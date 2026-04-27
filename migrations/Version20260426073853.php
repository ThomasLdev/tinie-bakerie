<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260426073853 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE ingredient_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE ingredient_group_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE ingredient_group_translation_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE ingredient_translation_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE ingredient (id INT NOT NULL, position INT DEFAULT 0 NOT NULL, base_quantity DOUBLE PRECISION DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, group_id INT DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_6BAF7870FE54D947 ON ingredient (group_id)');
        $this->addSql('CREATE TABLE ingredient_group (id INT NOT NULL, position INT DEFAULT 0 NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, recipe_id INT DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_74F2230459D8A214 ON ingredient_group (recipe_id)');
        $this->addSql('CREATE TABLE ingredient_group_translation (id INT NOT NULL, label VARCHAR(255) DEFAULT \'\' NOT NULL, locale VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, translatable_id INT DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_4F5AEB452C2AC5D3 ON ingredient_group_translation (translatable_id)');
        $this->addSql('CREATE TABLE ingredient_translation (id INT NOT NULL, name VARCHAR(255) DEFAULT \'\' NOT NULL, unit VARCHAR(255) DEFAULT \'\' NOT NULL, quantity_display VARCHAR(255) DEFAULT \'\' NOT NULL, locale VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, translatable_id INT DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_C1A8BF62C2AC5D3 ON ingredient_translation (translatable_id)');
        $this->addSql('CREATE TABLE recipe (cooking_time INT DEFAULT 0 NOT NULL, preparation_time INT DEFAULT 0 NOT NULL, difficulty VARCHAR(255) DEFAULT \'easy\' NOT NULL, servings INT DEFAULT 4 NOT NULL, id INT NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE TABLE recipe_step (tip_type VARCHAR(255) DEFAULT NULL, id INT NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE TABLE recipe_step_translation (tip_text TEXT DEFAULT \'\' NOT NULL, id INT NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE TABLE recipe_translation (notes TEXT DEFAULT \'\' NOT NULL, chef_note_title VARCHAR(255) DEFAULT \'\' NOT NULL, id INT NOT NULL, PRIMARY KEY (id))');
        $this->addSql('ALTER TABLE ingredient ADD CONSTRAINT FK_6BAF7870FE54D947 FOREIGN KEY (group_id) REFERENCES ingredient_group (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE ingredient_group ADD CONSTRAINT FK_74F2230459D8A214 FOREIGN KEY (recipe_id) REFERENCES recipe (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE ingredient_group_translation ADD CONSTRAINT FK_4F5AEB452C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES ingredient_group (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE ingredient_translation ADD CONSTRAINT FK_C1A8BF62C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES ingredient (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE recipe ADD CONSTRAINT FK_DA88B137BF396750 FOREIGN KEY (id) REFERENCES post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE recipe_step ADD CONSTRAINT FK_3CA2A4E3BF396750 FOREIGN KEY (id) REFERENCES post_section (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE recipe_step_translation ADD CONSTRAINT FK_F1D30669BF396750 FOREIGN KEY (id) REFERENCES post_section_translation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE recipe_translation ADD CONSTRAINT FK_609B0B0BBF396750 FOREIGN KEY (id) REFERENCES post_translation (id) ON DELETE CASCADE');
        // Existing fixtures = recipes. Add discriminator columns with default to fill, then drop default.
        $this->addSql("ALTER TABLE post ADD type VARCHAR(16) DEFAULT 'recipe' NOT NULL");
        $this->addSql('ALTER TABLE post ALTER COLUMN type DROP DEFAULT');
        $this->addSql('ALTER TABLE post DROP cooking_time');
        $this->addSql('ALTER TABLE post DROP difficulty');
        $this->addSql('ALTER TABLE post DROP preparation_time');
        $this->addSql("ALTER TABLE post_section ADD kind VARCHAR(16) DEFAULT 'recipe_step' NOT NULL");
        $this->addSql('ALTER TABLE post_section ALTER COLUMN kind DROP DEFAULT');
        $this->addSql("ALTER TABLE post_section_translation ADD kind VARCHAR(16) DEFAULT 'recipe_step' NOT NULL");
        $this->addSql('ALTER TABLE post_section_translation ALTER COLUMN kind DROP DEFAULT');
        $this->addSql("ALTER TABLE post_translation ADD type VARCHAR(16) DEFAULT 'recipe' NOT NULL");
        $this->addSql('ALTER TABLE post_translation ALTER COLUMN type DROP DEFAULT');
        $this->addSql('ALTER TABLE post_translation DROP notes');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE ingredient_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE ingredient_group_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE ingredient_group_translation_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE ingredient_translation_id_seq CASCADE');
        $this->addSql('ALTER TABLE ingredient DROP CONSTRAINT FK_6BAF7870FE54D947');
        $this->addSql('ALTER TABLE ingredient_group DROP CONSTRAINT FK_74F2230459D8A214');
        $this->addSql('ALTER TABLE ingredient_group_translation DROP CONSTRAINT FK_4F5AEB452C2AC5D3');
        $this->addSql('ALTER TABLE ingredient_translation DROP CONSTRAINT FK_C1A8BF62C2AC5D3');
        $this->addSql('ALTER TABLE recipe DROP CONSTRAINT FK_DA88B137BF396750');
        $this->addSql('ALTER TABLE recipe_step DROP CONSTRAINT FK_3CA2A4E3BF396750');
        $this->addSql('ALTER TABLE recipe_step_translation DROP CONSTRAINT FK_F1D30669BF396750');
        $this->addSql('ALTER TABLE recipe_translation DROP CONSTRAINT FK_609B0B0BBF396750');
        $this->addSql('DROP TABLE ingredient');
        $this->addSql('DROP TABLE ingredient_group');
        $this->addSql('DROP TABLE ingredient_group_translation');
        $this->addSql('DROP TABLE ingredient_translation');
        $this->addSql('DROP TABLE recipe');
        $this->addSql('DROP TABLE recipe_step');
        $this->addSql('DROP TABLE recipe_step_translation');
        $this->addSql('DROP TABLE recipe_translation');
        $this->addSql('ALTER TABLE post ADD cooking_time INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE post ADD difficulty VARCHAR(255) DEFAULT \'easy\' NOT NULL');
        $this->addSql('ALTER TABLE post ADD preparation_time INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE post DROP type');
        $this->addSql('ALTER TABLE post_section DROP kind');
        $this->addSql('ALTER TABLE post_section_translation DROP kind');
        $this->addSql('ALTER TABLE post_translation ADD notes TEXT DEFAULT \'\' NOT NULL');
        $this->addSql('ALTER TABLE post_translation DROP type');
    }
}
