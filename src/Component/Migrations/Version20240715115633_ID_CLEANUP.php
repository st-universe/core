<?php

declare(strict_types=1);

namespace Stu\Migrations;

use Override;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240715115633_ID_CLEANUP extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Removes the obsolete map_id and system_map_id columns.';
    }

    #[Override]
    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX location_system_map_idx');
        $this->addSql('DROP INDEX location_map_idx');
        $this->addSql('ALTER TABLE stu_location DROP map_id');
        $this->addSql('ALTER TABLE stu_location DROP starsystem_map_id');
        $this->addSql('ALTER TABLE stu_anomaly DROP map_id');
        $this->addSql('ALTER TABLE stu_anomaly DROP starsystem_map_id');
        $this->addSql('ALTER TABLE stu_buoy DROP map_id');
        $this->addSql('ALTER TABLE stu_buoy DROP sys_map_id');
        $this->addSql('ALTER TABLE stu_flight_sig DROP map_id');
        $this->addSql('ALTER TABLE stu_flight_sig DROP starsystem_map_id');
        $this->addSql('ALTER TABLE stu_ships DROP map_id');
        $this->addSql('ALTER TABLE stu_ships DROP starsystem_map_id');
        $this->addSql('ALTER TABLE stu_tachyon_scan DROP map_id');
        $this->addSql('ALTER TABLE stu_tachyon_scan DROP starsystem_map_id');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_tachyon_scan ADD map_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE stu_tachyon_scan ADD starsystem_map_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE stu_ships ADD map_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE stu_ships ADD starsystem_map_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE stu_flight_sig ADD map_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE stu_flight_sig ADD starsystem_map_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE stu_buoy ADD map_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE stu_buoy ADD sys_map_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE stu_anomaly ADD map_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE stu_anomaly ADD starsystem_map_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE stu_location ADD map_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE stu_location ADD starsystem_map_id INT DEFAULT NULL');
        $this->addSql('CREATE INDEX location_system_map_idx ON stu_location (starsystem_map_id)');
        $this->addSql('CREATE INDEX location_map_idx ON stu_location (map_id)');
    }
}
