<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211019190357 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE details_request_review (id INT AUTO_INCREMENT NOT NULL, request_review_id INT NOT NULL, document_id INT NOT NULL, status_document INT NOT NULL, comment LONGTEXT DEFAULT NULL, INDEX IDX_533922B175DF673C (request_review_id), INDEX IDX_533922B1C33F7837 (document_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE details_request_review ADD CONSTRAINT FK_533922B175DF673C FOREIGN KEY (request_review_id) REFERENCES request_review (id)');
        $this->addSql('ALTER TABLE details_request_review ADD CONSTRAINT FK_533922B1C33F7837 FOREIGN KEY (document_id) REFERENCES document (id)');
        $this->addSql('ALTER TABLE request_review ADD user_csb_id INT NOT NULL');
        $this->addSql('ALTER TABLE request_review ADD CONSTRAINT FK_321A423DECAD7EDF FOREIGN KEY (user_csb_id) REFERENCES user_csb (id)');
        $this->addSql('CREATE INDEX IDX_321A423DECAD7EDF ON request_review (user_csb_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE details_request_review');
        $this->addSql('ALTER TABLE request_review DROP FOREIGN KEY FK_321A423DECAD7EDF');
        $this->addSql('DROP INDEX IDX_321A423DECAD7EDF ON request_review');
        $this->addSql('ALTER TABLE request_review DROP user_csb_id');
    }
}
