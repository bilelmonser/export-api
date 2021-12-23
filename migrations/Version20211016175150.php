<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211016175150 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_csb (id INT NOT NULL, legal_name VARCHAR(255) DEFAULT NULL, legal_registration_number VARCHAR(255) DEFAULT NULL, legal_registration_date VARCHAR(255) DEFAULT NULL, legal_form VARCHAR(10) DEFAULT NULL, legal_share_capital INT DEFAULT NULL, address1 VARCHAR(255) DEFAULT NULL, address2 VARCHAR(255) DEFAULT NULL, address3 VARCHAR(255) DEFAULT NULL, postcode VARCHAR(255) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, country VARCHAR(255) DEFAULT NULL, email VARCHAR(255) NOT NULL, phone VARCHAR(255) DEFAULT NULL, legal_number_of_employee_range VARCHAR(255) DEFAULT NULL, legal_sector VARCHAR(255) DEFAULT NULL, legal_tva_number VARCHAR(255) DEFAULT NULL, legal_annual_turn_over VARCHAR(255) DEFAULT NULL, legal_net_income_range VARCHAR(255) DEFAULT NULL, specified_usperson INT DEFAULT NULL, user_tag VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE user_csb');
    }
}
