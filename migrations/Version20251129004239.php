<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251129004239 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE crew (id SERIAL NOT NULL, ship_id INT DEFAULT NULL, name VARCHAR(100) NOT NULL, surname VARCHAR(100) NOT NULL, nickname VARCHAR(100) DEFAULT NULL, birth_year INT DEFAULT NULL, birth_day INT DEFAULT NULL, birth_world VARCHAR(100) DEFAULT NULL, code VARCHAR(36) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_894940B2C256317D ON crew (ship_id)');
        $this->addSql('CREATE TABLE crew_ship_role (crew_id INT NOT NULL, ship_role_id INT NOT NULL, PRIMARY KEY(crew_id, ship_role_id))');
        $this->addSql('CREATE INDEX IDX_C71AD6D25FE259F6 ON crew_ship_role (crew_id)');
        $this->addSql('CREATE INDEX IDX_C71AD6D2D82E12C1 ON crew_ship_role (ship_role_id)');
        $this->addSql('CREATE TABLE insurance (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, annual_cost NUMERIC(11, 2) NOT NULL, coverage JSON DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE interest_rate (id SERIAL NOT NULL, duration INT NOT NULL, price_multiplier NUMERIC(11, 2) NOT NULL, price_divider INT NOT NULL, annual_interest_rate NUMERIC(11, 2) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE mortgage (id SERIAL NOT NULL, ship_id INT NOT NULL, interest_rate_id INT NOT NULL, insurance_id INT DEFAULT NULL, code VARCHAR(36) NOT NULL, name VARCHAR(100) NOT NULL, start_day INT NOT NULL, start_year INT NOT NULL, ship_shares INT DEFAULT NULL, advance_payment NUMERIC(11, 2) DEFAULT NULL, discount NUMERIC(11, 2) DEFAULT NULL, signed BOOLEAN DEFAULT false NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E10ABAD0C256317D ON mortgage (ship_id)');
        $this->addSql('CREATE INDEX IDX_E10ABAD0B3E3E851 ON mortgage (interest_rate_id)');
        $this->addSql('CREATE INDEX IDX_E10ABAD0D1E63CD1 ON mortgage (insurance_id)');
        $this->addSql('CREATE TABLE mortgage_installment (id SERIAL NOT NULL, mortgage_id INT NOT NULL, code VARCHAR(36) NOT NULL, payment_day INT NOT NULL, payment_year INT NOT NULL, payment NUMERIC(10, 2) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_CE3D2EB915375FCD ON mortgage_installment (mortgage_id)');
        $this->addSql('CREATE TABLE ship (id SERIAL NOT NULL, code UUID NOT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, class VARCHAR(255) NOT NULL, price NUMERIC(11, 2) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE ship_role (id SERIAL NOT NULL, code VARCHAR(4) NOT NULL, name VARCHAR(100) NOT NULL, description VARCHAR(1000) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('COMMENT ON COLUMN messenger_messages.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.available_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.delivered_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$
            BEGIN
                PERFORM pg_notify(\'messenger_messages\', NEW.queue_name::text);
                RETURN NEW;
            END;
        $$ LANGUAGE plpgsql;');
        $this->addSql('DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;');
        $this->addSql('CREATE TRIGGER notify_trigger AFTER INSERT OR UPDATE ON messenger_messages FOR EACH ROW EXECUTE PROCEDURE notify_messenger_messages();');
        $this->addSql('ALTER TABLE crew ADD CONSTRAINT FK_894940B2C256317D FOREIGN KEY (ship_id) REFERENCES ship (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE crew_ship_role ADD CONSTRAINT FK_C71AD6D25FE259F6 FOREIGN KEY (crew_id) REFERENCES crew (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE crew_ship_role ADD CONSTRAINT FK_C71AD6D2D82E12C1 FOREIGN KEY (ship_role_id) REFERENCES ship_role (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mortgage ADD CONSTRAINT FK_E10ABAD0C256317D FOREIGN KEY (ship_id) REFERENCES ship (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mortgage ADD CONSTRAINT FK_E10ABAD0B3E3E851 FOREIGN KEY (interest_rate_id) REFERENCES interest_rate (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mortgage ADD CONSTRAINT FK_E10ABAD0D1E63CD1 FOREIGN KEY (insurance_id) REFERENCES insurance (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mortgage_installment ADD CONSTRAINT FK_CE3D2EB915375FCD FOREIGN KEY (mortgage_id) REFERENCES mortgage (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE crew DROP CONSTRAINT FK_894940B2C256317D');
        $this->addSql('ALTER TABLE crew_ship_role DROP CONSTRAINT FK_C71AD6D25FE259F6');
        $this->addSql('ALTER TABLE crew_ship_role DROP CONSTRAINT FK_C71AD6D2D82E12C1');
        $this->addSql('ALTER TABLE mortgage DROP CONSTRAINT FK_E10ABAD0C256317D');
        $this->addSql('ALTER TABLE mortgage DROP CONSTRAINT FK_E10ABAD0B3E3E851');
        $this->addSql('ALTER TABLE mortgage DROP CONSTRAINT FK_E10ABAD0D1E63CD1');
        $this->addSql('ALTER TABLE mortgage_installment DROP CONSTRAINT FK_CE3D2EB915375FCD');
        $this->addSql('DROP TABLE crew');
        $this->addSql('DROP TABLE crew_ship_role');
        $this->addSql('DROP TABLE insurance');
        $this->addSql('DROP TABLE interest_rate');
        $this->addSql('DROP TABLE mortgage');
        $this->addSql('DROP TABLE mortgage_installment');
        $this->addSql('DROP TABLE ship');
        $this->addSql('DROP TABLE ship_role');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
