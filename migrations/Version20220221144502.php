<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220221144502 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE public.accountancy_practice_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE public.accounting_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE public.analytical_section_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE public.batch_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE public.company_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE public.company_information_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE public.financial_account_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE public.financial_period_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE public.ibiza_model_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE public.invoicing_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE public.journal_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE public.sage_model_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE public.user_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE public.accountancy_practice (id INT NOT NULL, sage_model_id INT DEFAULT NULL, sage_id VARCHAR(255) NOT NULL, business_id VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, origin_sage_application VARCHAR(255) NOT NULL, contact_email VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_88D44A1C4420DC09 ON public.accountancy_practice (sage_model_id)');
        $this->addSql('CREATE TABLE public.accounting (id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE public.analytical_section (id INT NOT NULL, company_id INT DEFAULT NULL, peiod_id INT DEFAULT NULL, code VARCHAR(255) NOT NULL, label VARCHAR(255) DEFAULT NULL, axe VARCHAR(255) DEFAULT NULL, super_section INT NOT NULL, uuid VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_32E0515979B1AD6 ON public.analytical_section (company_id)');
        $this->addSql('CREATE INDEX IDX_32E0515921EFC3 ON public.analytical_section (peiod_id)');
        $this->addSql('CREATE TABLE public.batch (id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE public.company (id INT NOT NULL, accountancy_practice_id INT DEFAULT NULL, sage_id VARCHAR(255) NOT NULL, business_id VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, is_accountancy_practice BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_749BC7F3AFE563CD ON public.company (accountancy_practice_id)');
        $this->addSql('CREATE TABLE public.company_information (id INT NOT NULL, company_id INT DEFAULT NULL, peiod_id INT DEFAULT NULL, uuid VARCHAR(255) NOT NULL, ape VARCHAR(255) DEFAULT NULL, naf VARCHAR(255) DEFAULT NULL, siret VARCHAR(255) DEFAULT NULL, siren VARCHAR(255) DEFAULT NULL, tax_system VARCHAR(255) DEFAULT NULL, tax_period VARCHAR(255) DEFAULT NULL, fiscal_system VARCHAR(255) DEFAULT NULL, fiscal_status VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_58414EAD979B1AD6 ON public.company_information (company_id)');
        $this->addSql('CREATE INDEX IDX_58414EAD921EFC3 ON public.company_information (peiod_id)');
        $this->addSql('CREATE TABLE public.financial_account (id INT NOT NULL, company_id INT DEFAULT NULL, peiod_id INT DEFAULT NULL, normalized_trading_account_type VARCHAR(255) NOT NULL, extras_collective_account_from VARCHAR(255) DEFAULT NULL, extras_collective_account_to VARCHAR(255) DEFAULT NULL, type INT NOT NULL, fin_acc_key VARCHAR(255) DEFAULT NULL, name VARCHAR(255) NOT NULL, extras_lettrable_account INT DEFAULT NULL, extras_with_quantities INT NOT NULL, locked BOOLEAN NOT NULL, cpt1 VARCHAR(255) DEFAULT NULL, cpt2 VARCHAR(255) DEFAULT NULL, uuid VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_1C623AA7979B1AD6 ON public.financial_account (company_id)');
        $this->addSql('CREATE INDEX IDX_1C623AA7921EFC3 ON public.financial_account (peiod_id)');
        $this->addSql('CREATE TABLE public.financial_period (id INT NOT NULL, company_id INT DEFAULT NULL, code VARCHAR(255) NOT NULL, financial_period_name VARCHAR(255) NOT NULL, start_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, end_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, closed BOOLEAN NOT NULL, extras_first_financial_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, extras_fiscal_end_of_the_first_fiscal_period TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, extras_account_label_length INT NOT NULL, extras_trading_account_length INT NOT NULL, extras_accounting_line_label_length INT NOT NULL, extras_account_length INT NOT NULL, extras_authorization_alpha_accounts BOOLEAN NOT NULL, extras_amounts_length INT NOT NULL, extras_with_quantities BOOLEAN NOT NULL, extras_with_due_dates BOOLEAN NOT NULL, extras_with_multiple_due_dates BOOLEAN NOT NULL, uuid VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_FA2BCE10979B1AD6 ON public.financial_period (company_id)');
        $this->addSql('CREATE TABLE public.ibiza_model (id INT NOT NULL, config1 VARCHAR(255) DEFAULT NULL, config2 VARCHAR(255) DEFAULT NULL, config3 VARCHAR(255) DEFAULT NULL, config4 VARCHAR(255) DEFAULT NULL, config5 VARCHAR(255) DEFAULT NULL, config6 VARCHAR(255) DEFAULT NULL, config7 VARCHAR(255) DEFAULT NULL, config8 VARCHAR(255) DEFAULT NULL, config9 VARCHAR(255) NOT NULL, config10 VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE public.invoicing (id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE public.journal (id INT NOT NULL, company_id INT DEFAULT NULL, peiod_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, short_name VARCHAR(255) NOT NULL, original_journal_type VARCHAR(255) NOT NULL, normalized_journal_type VARCHAR(255) NOT NULL, accounting_document_length INT NOT NULL, bank_account VARCHAR(255) DEFAULT NULL, accounts_forbidden VARCHAR(255) NOT NULL, without_propagation_date BOOLEAN NOT NULL, without_propagation_reference BOOLEAN NOT NULL, lock_end_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, uuid VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_FA8329F1979B1AD6 ON public.journal (company_id)');
        $this->addSql('CREATE INDEX IDX_FA8329F1921EFC3 ON public.journal (peiod_id)');
        $this->addSql('CREATE TABLE public.sage_model (id INT NOT NULL, user_id INT DEFAULT NULL, url_auth VARCHAR(255) DEFAULT NULL, grant_type VARCHAR(255) DEFAULT NULL, client_id VARCHAR(255) DEFAULT NULL, client_secret VARCHAR(255) DEFAULT NULL, audiance VARCHAR(255) DEFAULT NULL, app_id VARCHAR(255) DEFAULT NULL, token TEXT DEFAULT NULL, expiredtoken TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, accountancy_practice VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FF1D2F52AABEB54B ON public.sage_model (accountancy_practice)');
        $this->addSql('CREATE INDEX IDX_FF1D2F52A76ED395 ON public.sage_model (user_id)');
        $this->addSql('CREATE TABLE public."user" (id INT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, api_token VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_327C5DE7E7927C74 ON public."user" (email)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_327C5DE77BA2F5EB ON public."user" (api_token)');
        $this->addSql('ALTER TABLE public.accountancy_practice ADD CONSTRAINT FK_88D44A1C4420DC09 FOREIGN KEY (sage_model_id) REFERENCES public.sage_model (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE public.analytical_section ADD CONSTRAINT FK_32E0515979B1AD6 FOREIGN KEY (company_id) REFERENCES public.company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE public.analytical_section ADD CONSTRAINT FK_32E0515921EFC3 FOREIGN KEY (peiod_id) REFERENCES public.financial_period (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE public.company ADD CONSTRAINT FK_749BC7F3AFE563CD FOREIGN KEY (accountancy_practice_id) REFERENCES public.accountancy_practice (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE public.company_information ADD CONSTRAINT FK_58414EAD979B1AD6 FOREIGN KEY (company_id) REFERENCES public.company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE public.company_information ADD CONSTRAINT FK_58414EAD921EFC3 FOREIGN KEY (peiod_id) REFERENCES public.financial_period (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE public.financial_account ADD CONSTRAINT FK_1C623AA7979B1AD6 FOREIGN KEY (company_id) REFERENCES public.company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE public.financial_account ADD CONSTRAINT FK_1C623AA7921EFC3 FOREIGN KEY (peiod_id) REFERENCES public.financial_period (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE public.financial_period ADD CONSTRAINT FK_FA2BCE10979B1AD6 FOREIGN KEY (company_id) REFERENCES public.company (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE public.journal ADD CONSTRAINT FK_FA8329F1979B1AD6 FOREIGN KEY (company_id) REFERENCES public.company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE public.journal ADD CONSTRAINT FK_FA8329F1921EFC3 FOREIGN KEY (peiod_id) REFERENCES public.financial_period (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE public.sage_model ADD CONSTRAINT FK_FF1D2F52A76ED395 FOREIGN KEY (user_id) REFERENCES public."user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE public.company DROP CONSTRAINT FK_749BC7F3AFE563CD');
        $this->addSql('ALTER TABLE public.analytical_section DROP CONSTRAINT FK_32E0515979B1AD6');
        $this->addSql('ALTER TABLE public.company_information DROP CONSTRAINT FK_58414EAD979B1AD6');
        $this->addSql('ALTER TABLE public.financial_account DROP CONSTRAINT FK_1C623AA7979B1AD6');
        $this->addSql('ALTER TABLE public.financial_period DROP CONSTRAINT FK_FA2BCE10979B1AD6');
        $this->addSql('ALTER TABLE public.journal DROP CONSTRAINT FK_FA8329F1979B1AD6');
        $this->addSql('ALTER TABLE public.analytical_section DROP CONSTRAINT FK_32E0515921EFC3');
        $this->addSql('ALTER TABLE public.company_information DROP CONSTRAINT FK_58414EAD921EFC3');
        $this->addSql('ALTER TABLE public.financial_account DROP CONSTRAINT FK_1C623AA7921EFC3');
        $this->addSql('ALTER TABLE public.journal DROP CONSTRAINT FK_FA8329F1921EFC3');
        $this->addSql('ALTER TABLE public.accountancy_practice DROP CONSTRAINT FK_88D44A1C4420DC09');
        $this->addSql('ALTER TABLE public.sage_model DROP CONSTRAINT FK_FF1D2F52A76ED395');
        $this->addSql('DROP SEQUENCE public.accountancy_practice_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE public.accounting_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE public.analytical_section_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE public.batch_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE public.company_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE public.company_information_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE public.financial_account_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE public.financial_period_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE public.ibiza_model_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE public.invoicing_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE public.journal_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE public.sage_model_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE public.user_id_seq CASCADE');
        $this->addSql('DROP TABLE public.accountancy_practice');
        $this->addSql('DROP TABLE public.accounting');
        $this->addSql('DROP TABLE public.analytical_section');
        $this->addSql('DROP TABLE public.batch');
        $this->addSql('DROP TABLE public.company');
        $this->addSql('DROP TABLE public.company_information');
        $this->addSql('DROP TABLE public.financial_account');
        $this->addSql('DROP TABLE public.financial_period');
        $this->addSql('DROP TABLE public.ibiza_model');
        $this->addSql('DROP TABLE public.invoicing');
        $this->addSql('DROP TABLE public.journal');
        $this->addSql('DROP TABLE public.sage_model');
        $this->addSql('DROP TABLE public."user"');
    }
}
