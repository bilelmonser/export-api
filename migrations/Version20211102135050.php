<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211102135050 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE tax_residences (id INT AUTO_INCREMENT NOT NULL, user_csb_id INT NOT NULL, country VARCHAR(3) DEFAULT NULL, tax_payer_id VARCHAR(255) DEFAULT NULL, liability_waiver TINYINT(1) NOT NULL, created_date VARCHAR(255) NOT NULL, last_update VARCHAR(255) DEFAULT NULL, deleted_date VARCHAR(255) DEFAULT NULL, INDEX IDX_62FD69FDECAD7EDF (user_csb_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tax_residences ADD CONSTRAINT FK_62FD69FDECAD7EDF FOREIGN KEY (user_csb_id) REFERENCES user_csb (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE tax_residences');
    }
}
