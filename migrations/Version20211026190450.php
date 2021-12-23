<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211026190450 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE wallet CHANGE wallet_id wallet_id INT NOT NULL, CHANGE code_status code_status INT NOT NULL, CHANGE wallet_tag wallet_tag VARCHAR(255) NOT NULL, CHANGE user_lastname user_lastname VARCHAR(255) NOT NULL, CHANGE user_firstname user_firstname VARCHAR(255) NOT NULL, CHANGE event_alias event_alias VARCHAR(255) NOT NULL, CHANGE event_payin_start_date event_payin_start_date DATE NOT NULL, CHANGE event_payin_end_date event_payin_end_date DATE NOT NULL, CHANGE contract_signed contract_signed INT NOT NULL, CHANGE bic bic VARCHAR(255) NOT NULL, CHANGE iban iban VARCHAR(255) NOT NULL, CHANGE currency currency VARCHAR(10) NOT NULL, CHANGE created_date created_date DATETIME NOT NULL, CHANGE payin_count payin_count INT DEFAULT 0, CHANGE payout_count payout_count INT DEFAULT 0 NOT NULL, CHANGE transfer_count transfer_count INT DEFAULT 0 NOT NULL, CHANGE total_rows total_rows INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE wallet CHANGE wallet_id wallet_id INT DEFAULT NULL, CHANGE code_status code_status INT DEFAULT NULL, CHANGE wallet_tag wallet_tag VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE user_lastname user_lastname VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE user_firstname user_firstname VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE event_alias event_alias VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE event_payin_start_date event_payin_start_date DATE DEFAULT NULL, CHANGE event_payin_end_date event_payin_end_date DATE DEFAULT NULL, CHANGE contract_signed contract_signed INT DEFAULT NULL, CHANGE bic bic VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE iban iban VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE currency currency VARCHAR(10) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE created_date created_date DATETIME DEFAULT NULL, CHANGE payin_count payin_count INT DEFAULT NULL, CHANGE payout_count payout_count INT DEFAULT NULL, CHANGE transfer_count transfer_count INT DEFAULT NULL, CHANGE total_rows total_rows INT DEFAULT NULL');
    }
}
