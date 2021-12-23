<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211019202704 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE details_request_review CHANGE status_document status_document INT DEFAULT NULL, CHANGE document_id document_id INT DEFAULT NULL, CHANGE user_csb_id user_csb_id INT DEFAULT NULL, CHANGE status_user_csb status_user_csb INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE details_request_review CHANGE document_id document_id INT NOT NULL, CHANGE status_document status_document INT NOT NULL, CHANGE user_csb_id user_csb_id INT NOT NULL, CHANGE status_user_csb status_user_csb INT NOT NULL');
    }
}
