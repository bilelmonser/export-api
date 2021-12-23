<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211025113803 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE wallet (wallet_id INT NOT NULL, wallet_type_id INT NOT NULL, wallet_status VARCHAR(10) NOT NULL, code_status INT DEFAULT NULL, information_status VARCHAR(255) DEFAULT NULL, wallet_tag VARCHAR(255) DEFAULT NULL, user_id INT NOT NULL, user_lastname VARCHAR(255) DEFAULT NULL, user_firstname VARCHAR(255) DEFAULT NULL, joint_user_id INT NOT NULL, tariff_id INT NOT NULL, event_name VARCHAR(255) NOT NULL, event_alias VARCHAR(255) DEFAULT NULL, event_date DATETIME NOT NULL, event_message VARCHAR(255) DEFAULT NULL, PRIMARY KEY(wallet_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A76ECAD7EDF');
        $this->addSql('DROP INDEX IDX_D8698A76ECAD7EDF ON document');
        $this->addSql('ALTER TABLE document DROP user_csb_id');
        $this->addSql('ALTER TABLE request_review DROP FOREIGN KEY FK_321A423DECAD7EDF');
        $this->addSql('DROP INDEX IDX_321A423DECAD7EDF ON request_review');
        $this->addSql('ALTER TABLE request_review DROP user_csb_id');
        $this->addSql('ALTER TABLE user_csb ADD auth_user_id INT NOT NULL, CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE user_csb ADD CONSTRAINT FK_F968E7A1E94AF366 FOREIGN KEY (auth_user_id) REFERENCES auth_user (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F968E7A1E94AF366 ON user_csb (auth_user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE wallet');
        $this->addSql('ALTER TABLE document ADD user_csb_id INT NOT NULL');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A76ECAD7EDF FOREIGN KEY (user_csb_id) REFERENCES user_csb (id)');
        $this->addSql('CREATE INDEX IDX_D8698A76ECAD7EDF ON document (user_csb_id)');
        $this->addSql('ALTER TABLE request_review ADD user_csb_id INT NOT NULL');
        $this->addSql('ALTER TABLE request_review ADD CONSTRAINT FK_321A423DECAD7EDF FOREIGN KEY (user_csb_id) REFERENCES user_csb (id)');
        $this->addSql('CREATE INDEX IDX_321A423DECAD7EDF ON request_review (user_csb_id)');
        $this->addSql('ALTER TABLE user_csb DROP FOREIGN KEY FK_F968E7A1E94AF366');
        $this->addSql('DROP INDEX UNIQ_F968E7A1E94AF366 ON user_csb');
        $this->addSql('ALTER TABLE user_csb DROP auth_user_id, CHANGE id id INT NOT NULL');
    }
}
