<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260621210336 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create vehicle_makes, vehicle_models, vehicles and vehicle_users tables with UUIDv7 primary keys';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE vehicle_makes (name VARCHAR(255) NOT NULL, id VARCHAR(36) NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D3B1CFCE5E237E06 ON vehicle_makes (name)');
        $this->addSql('CREATE TABLE vehicle_models (name VARCHAR(255) NOT NULL, id VARCHAR(36) NOT NULL, make_id VARCHAR(36) NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_4D0831DACFBF73EB ON vehicle_models (make_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_vehicle_models_make_id_name ON vehicle_models (make_id, name)');
        $this->addSql('CREATE TABLE vehicle_users (user_id VARCHAR(36) NOT NULL, role VARCHAR(20) NOT NULL, id VARCHAR(36) NOT NULL, vehicle_id VARCHAR(36) NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_762C2505545317D1 ON vehicle_users (vehicle_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_vehicle_users_vehicle_id_user_id ON vehicle_users (vehicle_id, user_id)');
        $this->addSql('CREATE TABLE vehicles (plate VARCHAR(20) NOT NULL, year SMALLINT NOT NULL, type VARCHAR(50) NOT NULL, owner_id VARCHAR(36) NOT NULL, vin VARCHAR(17) DEFAULT NULL, custom_make VARCHAR(255) DEFAULT NULL, custom_model VARCHAR(255) DEFAULT NULL, id VARCHAR(36) NOT NULL, make_id VARCHAR(36) DEFAULT NULL, model_id VARCHAR(36) DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1FCE69FAB1085141 ON vehicles (vin)');
        $this->addSql('CREATE INDEX IDX_1FCE69FACFBF73EB ON vehicles (make_id)');
        $this->addSql('CREATE INDEX IDX_1FCE69FA7975B7E7 ON vehicles (model_id)');
        $this->addSql('ALTER TABLE vehicle_models ADD CONSTRAINT FK_4D0831DACFBF73EB FOREIGN KEY (make_id) REFERENCES vehicle_makes (id)');
        $this->addSql('ALTER TABLE vehicle_users ADD CONSTRAINT FK_762C2505545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicles (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE vehicles ADD CONSTRAINT FK_1FCE69FACFBF73EB FOREIGN KEY (make_id) REFERENCES vehicle_makes (id)');
        $this->addSql('ALTER TABLE vehicles ADD CONSTRAINT FK_1FCE69FA7975B7E7 FOREIGN KEY (model_id) REFERENCES vehicle_models (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE vehicle_models DROP CONSTRAINT FK_4D0831DACFBF73EB');
        $this->addSql('ALTER TABLE vehicle_users DROP CONSTRAINT FK_762C2505545317D1');
        $this->addSql('ALTER TABLE vehicles DROP CONSTRAINT FK_1FCE69FACFBF73EB');
        $this->addSql('ALTER TABLE vehicles DROP CONSTRAINT FK_1FCE69FA7975B7E7');
        $this->addSql('DROP TABLE vehicle_makes');
        $this->addSql('DROP TABLE vehicle_models');
        $this->addSql('DROP TABLE vehicle_users');
        $this->addSql('DROP TABLE vehicles');
    }
}
