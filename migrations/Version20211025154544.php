<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211025154544 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_csb ADD parent_user_id INT DEFAULT NULL, ADD parent_type VARCHAR(15) DEFAULT NULL, ADD controlling_person_type INT DEFAULT NULL, ADD employee_type INT DEFAULT NULL, ADD title VARCHAR(5) DEFAULT NULL, ADD firstname VARCHAR(255) DEFAULT NULL, ADD lastname VARCHAR(255) DEFAULT NULL, ADD middle_names VARCHAR(255) DEFAULT NULL, ADD birthday VARCHAR(20) DEFAULT NULL, ADD state VARCHAR(255) DEFAULT NULL, ADD country_name VARCHAR(255) DEFAULT NULL, ADD mobile VARCHAR(255) DEFAULT NULL, ADD nationality VARCHAR(255) DEFAULT NULL, ADD nationality_other VARCHAR(255) DEFAULT NULL, ADD place_of_birth VARCHAR(255) DEFAULT NULL, ADD birth_country VARCHAR(255) DEFAULT NULL, ADD occupation VARCHAR(255) DEFAULT NULL, ADD income_range VARCHAR(255) DEFAULT NULL, ADD legal_name_embossed VARCHAR(255) DEFAULT NULL, ADD effective_beneficiary INT DEFAULT NULL, ADD kyc_level INT DEFAULT NULL, ADD kyc_review INT DEFAULT NULL, ADD kyc_review_comment LONGTEXT DEFAULT NULL, ADD is_freezed INT DEFAULT NULL, ADD is_frozen INT DEFAULT NULL, ADD language VARCHAR(255) DEFAULT NULL, ADD opt_in_mailing INT DEFAULT NULL, ADD sepa_creditor_identifier VARCHAR(255) DEFAULT NULL, ADD tax_number VARCHAR(255) DEFAULT NULL, ADD tax_residence VARCHAR(255) DEFAULT NULL, ADD position VARCHAR(255) DEFAULT NULL, ADD personal_assets VARCHAR(255) DEFAULT NULL, ADD activity_outside_eu INT DEFAULT NULL, ADD economic_sanctions INT DEFAULT NULL, ADD resident_countries_sanctions INT DEFAULT NULL, ADD involved_sanctions INT DEFAULT NULL, ADD sanctions_questionnaire_date VARCHAR(255) DEFAULT NULL, ADD timezone VARCHAR(255) DEFAULT NULL, ADD created_date VARCHAR(255) DEFAULT NULL, ADD modified_date VARCHAR(255) DEFAULT NULL, ADD wallet_count INT DEFAULT NULL, ADD payin_count INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_csb DROP parent_user_id, DROP parent_type, DROP controlling_person_type, DROP employee_type, DROP title, DROP firstname, DROP lastname, DROP middle_names, DROP birthday, DROP state, DROP country_name, DROP mobile, DROP nationality, DROP nationality_other, DROP place_of_birth, DROP birth_country, DROP occupation, DROP income_range, DROP legal_name_embossed, DROP effective_beneficiary, DROP kyc_level, DROP kyc_review, DROP kyc_review_comment, DROP is_freezed, DROP is_frozen, DROP language, DROP opt_in_mailing, DROP sepa_creditor_identifier, DROP tax_number, DROP tax_residence, DROP position, DROP personal_assets, DROP activity_outside_eu, DROP economic_sanctions, DROP resident_countries_sanctions, DROP involved_sanctions, DROP sanctions_questionnaire_date, DROP timezone, DROP created_date, DROP modified_date, DROP wallet_count, DROP payin_count');
    }
}
