<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211028134501 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE wallet ADD user_csb_id INT NOT NULL');
        $this->addSql('ALTER TABLE wallet ADD CONSTRAINT FK_7C68921FECAD7EDF FOREIGN KEY (user_csb_id) REFERENCES user_csb (id)');
        $this->addSql('CREATE INDEX IDX_7C68921FECAD7EDF ON wallet (user_csb_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE wallet DROP FOREIGN KEY FK_7C68921FECAD7EDF');
        $this->addSql('DROP INDEX IDX_7C68921FECAD7EDF ON wallet');
        $this->addSql('ALTER TABLE wallet DROP user_csb_id');
    }
}
