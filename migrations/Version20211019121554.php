<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211019121554 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE document ADD treezor_status_validation INT NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE created_date created_date VARCHAR(255) DEFAULT NULL, CHANGE modified_date modified_date VARCHAR(255) DEFAULT NULL, CHANGE total_rows total_rows VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE document DROP treezor_status_validation');
        $this->addSql('ALTER TABLE user CHANGE created_date created_date DATETIME DEFAULT NULL, CHANGE modified_date modified_date DATETIME DEFAULT NULL, CHANGE total_rows total_rows INT DEFAULT NULL');
    }
}
