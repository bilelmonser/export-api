<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211017114246 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE document ADD users_csb_id INT NOT NULL, CHANGE file_status file_status INT DEFAULT 2 NOT NULL');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A768618EB72 FOREIGN KEY (users_csb_id) REFERENCES user_csb (id)');
        $this->addSql('CREATE INDEX IDX_D8698A768618EB72 ON document (users_csb_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A768618EB72');
        $this->addSql('DROP INDEX IDX_D8698A768618EB72 ON document');
        $this->addSql('ALTER TABLE document DROP users_csb_id, CHANGE file_status file_status INT NOT NULL');
    }
}
