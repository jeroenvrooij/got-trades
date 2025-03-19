<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250319134942 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_card_printings (card_printing_unique_id VARCHAR(21) NOT NULL, user_id UUID NOT NULL, collection_amount INT NOT NULL, PRIMARY KEY(user_id, card_printing_unique_id))');
        $this->addSql('CREATE INDEX IDX_2007DFCDA76ED395 ON user_card_printings (user_id)');
        $this->addSql('COMMENT ON COLUMN user_card_printings.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE user_card_printings ADD CONSTRAINT FK_2007DFCDA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE user_card_printings DROP CONSTRAINT FK_2007DFCDA76ED395');
        $this->addSql('DROP TABLE user_card_printings');
    }
}
