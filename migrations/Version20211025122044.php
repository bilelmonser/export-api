<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211025122044 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE document ADD user_csb_id INT NOT NULL');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A76ECAD7EDF FOREIGN KEY (user_csb_id) REFERENCES user_csb (id)');
        $this->addSql('CREATE INDEX IDX_D8698A76ECAD7EDF ON document (user_csb_id)');
        $this->addSql('ALTER TABLE request_review ADD user_csb_id INT NOT NULL');
        $this->addSql('ALTER TABLE request_review ADD CONSTRAINT FK_321A423DECAD7EDF FOREIGN KEY (user_csb_id) REFERENCES user_csb (id)');
        $this->addSql('CREATE INDEX IDX_321A423DECAD7EDF ON request_review (user_csb_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A76ECAD7EDF');
        $this->addSql('DROP INDEX IDX_D8698A76ECAD7EDF ON document');
        $this->addSql('ALTER TABLE document DROP user_csb_id');
        $this->addSql('ALTER TABLE request_review DROP FOREIGN KEY FK_321A423DECAD7EDF');
        $this->addSql('DROP INDEX IDX_321A423DECAD7EDF ON request_review');
        $this->addSql('ALTER TABLE request_review DROP user_csb_id');
    }
}
