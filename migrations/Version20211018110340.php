<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211018110340 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user (user_id INT NOT NULL, user_type_id INT DEFAULT NULL, user_status VARCHAR(255) DEFAULT NULL, user_tag VARCHAR(255) DEFAULT NULL, parent_user_id INT DEFAULT NULL, parent_type VARCHAR(255) DEFAULT NULL, controlling_person_type INT DEFAULT NULL, employee_type INT DEFAULT NULL, specified_usperson INT DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, firstname VARCHAR(255) DEFAULT NULL, lastname VARCHAR(255) DEFAULT NULL, middle_names VARCHAR(255) DEFAULT NULL, birthday VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, address1 VARCHAR(255) DEFAULT NULL, address2 VARCHAR(255) DEFAULT NULL, address3 VARCHAR(255) DEFAULT NULL, postcode VARCHAR(255) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, state VARCHAR(255) DEFAULT NULL, country VARCHAR(255) DEFAULT NULL, country_name VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, mobile VARCHAR(255) DEFAULT NULL, nationality VARCHAR(255) DEFAULT NULL, nationality_other VARCHAR(255) DEFAULT NULL, place_of_birth VARCHAR(255) DEFAULT NULL, birth_country VARCHAR(255) DEFAULT NULL, occupation VARCHAR(255) DEFAULT NULL, income_range VARCHAR(255) DEFAULT NULL, legal_name VARCHAR(255) DEFAULT NULL, legal_name_embossed VARCHAR(255) DEFAULT NULL, legal_registration_number VARCHAR(255) DEFAULT NULL, legal_tva_number VARCHAR(255) DEFAULT NULL, legal_registration_date VARCHAR(255) DEFAULT NULL, legal_form VARCHAR(255) DEFAULT NULL, legal_share_capital INT DEFAULT NULL, legal_sector VARCHAR(255) DEFAULT NULL, legal_annual_turn_over VARCHAR(255) DEFAULT NULL, legal_net_income_range VARCHAR(255) DEFAULT NULL, legal_number_of_employee_range VARCHAR(255) DEFAULT NULL, effective_beneficiary INT DEFAULT NULL, kyc_level INT DEFAULT NULL, kyc_review INT DEFAULT NULL, kyc_review_comment VARCHAR(255) DEFAULT NULL, is_freezed INT DEFAULT NULL, language VARCHAR(255) DEFAULT NULL, opt_in_mailing INT DEFAULT NULL, sepa_creditor_identifier VARCHAR(255) DEFAULT NULL, tax_number VARCHAR(255) DEFAULT NULL, tax_residence VARCHAR(255) DEFAULT NULL, position VARCHAR(255) DEFAULT NULL, personal_assets VARCHAR(255) DEFAULT NULL, activity_outside_eu INT DEFAULT NULL, economic_sanctions INT DEFAULT NULL, resident_countries_sanctions INT DEFAULT NULL, involved_sanctions INT DEFAULT NULL, sanctions_questionnaire_date VARCHAR(255) DEFAULT NULL, timezone VARCHAR(255) DEFAULT NULL, created_date DATETIME DEFAULT NULL, modified_date DATETIME DEFAULT NULL, wallet_count INT DEFAULT NULL, payin_count INT DEFAULT NULL, total_rows INT DEFAULT NULL, PRIMARY KEY(user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE user');
    }
}
