<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260623133141 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE invoices (file_path VARCHAR(255) NOT NULL, amount NUMERIC(10, 2) DEFAULT NULL, date DATE DEFAULT NULL, shop_name VARCHAR(255) DEFAULT NULL, description TEXT DEFAULT NULL, status VARCHAR(20) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, id UUID NOT NULL, vehicle_id VARCHAR(36) NOT NULL, uploaded_by CHAR(36) NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_6A2F2F95545317D1 ON invoices (vehicle_id)');
        $this->addSql('CREATE INDEX IDX_6A2F2F95E3E73126 ON invoices (uploaded_by)');
        $this->addSql('CREATE TABLE maintenance_records (service_date DATE NOT NULL, mileage INT DEFAULT NULL, type VARCHAR(100) NOT NULL, notes TEXT DEFAULT NULL, cost NUMERIC(10, 2) DEFAULT NULL, shop_name VARCHAR(255) DEFAULT NULL, next_service_date DATE DEFAULT NULL, verified BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, id UUID NOT NULL, vehicle_id VARCHAR(36) NOT NULL, invoice_id UUID DEFAULT NULL, created_by CHAR(36) NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_E56B1DD4545317D1 ON maintenance_records (vehicle_id)');
        $this->addSql('CREATE INDEX IDX_E56B1DD42989F1FD ON maintenance_records (invoice_id)');
        $this->addSql('CREATE INDEX IDX_E56B1DD4DE12AB56 ON maintenance_records (created_by)');
        $this->addSql('ALTER TABLE invoices ADD CONSTRAINT FK_6A2F2F95545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicles (id)');
        $this->addSql('ALTER TABLE invoices ADD CONSTRAINT FK_6A2F2F95E3E73126 FOREIGN KEY (uploaded_by) REFERENCES users (id)');
        $this->addSql('ALTER TABLE maintenance_records ADD CONSTRAINT FK_E56B1DD4545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicles (id)');
        $this->addSql('ALTER TABLE maintenance_records ADD CONSTRAINT FK_E56B1DD42989F1FD FOREIGN KEY (invoice_id) REFERENCES invoices (id)');
        $this->addSql('ALTER TABLE maintenance_records ADD CONSTRAINT FK_E56B1DD4DE12AB56 FOREIGN KEY (created_by) REFERENCES users (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invoices DROP CONSTRAINT FK_6A2F2F95545317D1');
        $this->addSql('ALTER TABLE invoices DROP CONSTRAINT FK_6A2F2F95E3E73126');
        $this->addSql('ALTER TABLE maintenance_records DROP CONSTRAINT FK_E56B1DD4545317D1');
        $this->addSql('ALTER TABLE maintenance_records DROP CONSTRAINT FK_E56B1DD42989F1FD');
        $this->addSql('ALTER TABLE maintenance_records DROP CONSTRAINT FK_E56B1DD4DE12AB56');
        $this->addSql('DROP TABLE invoices');
        $this->addSql('DROP TABLE maintenance_records');
    }
}
