<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261002114552 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add users.verified_at; accounts created before email verification existed are treated as verified';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users ADD verified_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('UPDATE users SET verified_at = created_at');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users DROP verified_at');
    }
}
