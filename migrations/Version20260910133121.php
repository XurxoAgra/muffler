<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260910133121 extends AbstractMigration
{
    /**
     * Initial catalog. Keys are language-agnostic; labels are resolved client side.
     * Periodicity and icon values are sensible defaults, not domain rules.
     *
     * @var list<array{string, string, string, int|null, int|null}>
     */
    private const CATALOG = [
        ['019eec10-0000-7000-8000-000000000001', 'oil_change', 'droplet', 12, 15000],
        ['019eec10-0000-7000-8000-000000000002', 'itv', 'clipboard-check', 12, null],
        ['019eec10-0000-7000-8000-000000000003', 'insurance', 'shield', 12, null],
        ['019eec10-0000-7000-8000-000000000004', 'tire_change', 'circle-dot', null, 40000],
        ['019eec10-0000-7000-8000-000000000005', 'brake_check', 'disc', 12, 20000],
        ['019eec10-0000-7000-8000-000000000006', 'battery', 'battery', 48, null],
        ['019eec10-0000-7000-8000-000000000007', 'air_filter', 'wind', 24, 30000],
        ['019eec10-0000-7000-8000-000000000008', 'timing_belt', 'timer', 120, 120000],
        ['019eec10-0000-7000-8000-000000000009', 'other', 'wrench', null, null],
    ];

    /**
     * Maps the free-text values that used to live in maintenance_records.type onto a
     * catalog key. Patterns are accent-free stems so they match both Spanish and English
     * legacy values; anything unmatched falls back to "other".
     *
     * @var array<string, list<string>>
     */
    private const LEGACY_TYPE_PATTERNS = [
        'air_filter' => ['%filtro%aire%', '%air%filter%'],
        'timing_belt' => ['%correa%', '%distribu%', '%timing%belt%'],
        'oil_change' => ['%aceit%', '%oil%'],
        'itv' => ['%itv%', '%inspec%'],
        'insurance' => ['%segur%', '%insurance%'],
        'tire_change' => ['%rueda%', '%neum%', '%tire%', '%tyre%'],
        'brake_check' => ['%freno%', '%pastilla%', '%brake%'],
        'battery' => ['%bater%', '%battery%'],
    ];

    public function getDescription(): string
    {
        return 'Add the maintenance_record_type catalog and replace maintenance_records.type with a foreign key to it';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE maintenance_record_type (key VARCHAR(50) NOT NULL, icon VARCHAR(50) DEFAULT NULL, default_periodicity_months INT DEFAULT NULL, default_periodicity_km INT DEFAULT NULL, active BOOLEAN NOT NULL, id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_634E2F5A8A90ABA9 ON maintenance_record_type (key)');

        foreach (self::CATALOG as [$id, $key, $icon, $months, $km]) {
            $this->addSql(
                'INSERT INTO maintenance_record_type (id, key, icon, default_periodicity_months, default_periodicity_km, active) VALUES (:id, :key, :icon, :months, :km, true)',
                ['id' => $id, 'key' => $key, 'icon' => $icon, 'months' => $months, 'km' => $km],
            );
        }

        $this->addSql('ALTER TABLE maintenance_records ADD maintenance_record_type_id UUID DEFAULT NULL');

        foreach (self::LEGACY_TYPE_PATTERNS as $key => $patterns) {
            $conditions = implode(' OR ', array_map(
                static fn (int $index) => "maintenance_records.type ILIKE :pattern{$index}",
                array_keys($patterns),
            ));

            $parameters = ['key' => $key];
            foreach ($patterns as $index => $pattern) {
                $parameters["pattern{$index}"] = $pattern;
            }

            $this->addSql(
                'UPDATE maintenance_records SET maintenance_record_type_id = (SELECT id FROM maintenance_record_type WHERE key = :key)'
                ." WHERE maintenance_record_type_id IS NULL AND ({$conditions})",
                $parameters,
            );
        }

        $this->addSql("UPDATE maintenance_records SET maintenance_record_type_id = (SELECT id FROM maintenance_record_type WHERE key = 'other') WHERE maintenance_record_type_id IS NULL");

        $this->addSql('ALTER TABLE maintenance_records ALTER maintenance_record_type_id SET NOT NULL');
        $this->addSql('ALTER TABLE maintenance_records DROP type');
        $this->addSql('ALTER TABLE maintenance_records ADD CONSTRAINT FK_E56B1DD484123ACF FOREIGN KEY (maintenance_record_type_id) REFERENCES maintenance_record_type (id)');
        $this->addSql('CREATE INDEX IDX_E56B1DD484123ACF ON maintenance_records (maintenance_record_type_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE maintenance_records ADD type VARCHAR(100) DEFAULT NULL');
        $this->addSql('UPDATE maintenance_records SET type = maintenance_record_type.key FROM maintenance_record_type WHERE maintenance_record_type.id = maintenance_records.maintenance_record_type_id');
        $this->addSql('ALTER TABLE maintenance_records ALTER type SET NOT NULL');
        $this->addSql('ALTER TABLE maintenance_records DROP CONSTRAINT FK_E56B1DD484123ACF');
        $this->addSql('DROP INDEX IDX_E56B1DD484123ACF');
        $this->addSql('ALTER TABLE maintenance_records DROP maintenance_record_type_id');
        $this->addSql('DROP TABLE maintenance_record_type');
    }
}
