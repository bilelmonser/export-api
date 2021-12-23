<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211026151316 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE wallet ADD id INT AUTO_INCREMENT NOT NULL, ADD event_payin_start_date DATE DEFAULT NULL, ADD event_payin_end_date DATE DEFAULT NULL, ADD contract_signed INT DEFAULT NULL, ADD bic VARCHAR(255) DEFAULT NULL, ADD iban VARCHAR(255) DEFAULT NULL, ADD url_image VARCHAR(255) DEFAULT NULL, ADD currency VARCHAR(10) DEFAULT NULL, ADD created_date DATETIME DEFAULT NULL, ADD modified_date DATETIME DEFAULT NULL, ADD payin_count INT DEFAULT NULL, ADD payout_count INT DEFAULT NULL, ADD transfer_count INT DEFAULT NULL, ADD solde INT DEFAULT NULL, ADD authorized_balance INT DEFAULT NULL, ADD total_rows INT DEFAULT NULL, CHANGE wallet_id wallet_id INT DEFAULT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE wallet MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE wallet DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE wallet DROP id, DROP event_payin_start_date, DROP event_payin_end_date, DROP contract_signed, DROP bic, DROP iban, DROP url_image, DROP currency, DROP created_date, DROP modified_date, DROP payin_count, DROP payout_count, DROP transfer_count, DROP solde, DROP authorized_balance, DROP total_rows, CHANGE wallet_id wallet_id INT NOT NULL');
        $this->addSql('ALTER TABLE wallet ADD PRIMARY KEY (wallet_id)');
    }
}
