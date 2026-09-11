<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260911120125 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add the mileage_records table holding the odometer history of each vehicle';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE mileage_records (mileage INT NOT NULL, recorded_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, source VARCHAR(30) NOT NULL, id UUID NOT NULL, vehicle_id VARCHAR(36) NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_56B2C894545317D1 ON mileage_records (vehicle_id)');
        $this->addSql('ALTER TABLE mileage_records ADD CONSTRAINT FK_56B2C894545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicles (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE mileage_records DROP CONSTRAINT FK_56B2C894545317D1');
        $this->addSql('DROP TABLE mileage_records');
    }
}
