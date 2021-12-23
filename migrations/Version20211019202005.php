<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211019202005 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE details_request_review DROP FOREIGN KEY FK_533922B1C33F7837');
        $this->addSql('DROP INDEX IDX_533922B1C33F7837 ON details_request_review');
        $this->addSql('ALTER TABLE details_request_review DROP document_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE details_request_review ADD document_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE details_request_review ADD CONSTRAINT FK_533922B1C33F7837 FOREIGN KEY (document_id) REFERENCES document (id)');
        $this->addSql('CREATE INDEX IDX_533922B1C33F7837 ON details_request_review (document_id)');
    }
}
