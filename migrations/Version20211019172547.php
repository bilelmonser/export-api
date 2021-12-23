<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211019172547 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE treezor_document (document_id INT NOT NULL, document_tag VARCHAR(255) DEFAULT NULL, document_status VARCHAR(255) DEFAULT NULL, document_type_id INT DEFAULT NULL, document_type VARCHAR(255) DEFAULT NULL, residence_id INT DEFAULT NULL, client_id INT DEFAULT NULL, user_id INT DEFAULT NULL, user_lastname VARCHAR(255) DEFAULT NULL, user_firstname VARCHAR(255) DEFAULT NULL, file_name VARCHAR(255) DEFAULT NULL, created_date VARCHAR(255) DEFAULT NULL, modified_date VARCHAR(255) DEFAULT NULL, code_status VARCHAR(255) DEFAULT NULL, PRIMARY KEY(document_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE treezor_document');
    }
}
