<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241212212958_Rename_SpacecraftEntities extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename former ship sub-entities.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_ship_system RENAME TO stu_spacecraft_system');
        $this->addSql('ALTER TABLE stu_rumps RENAME TO stu_rump');
        $this->addSql('ALTER TABLE stu_rump ALTER base_warpdrive SET NOT NULL');
        $this->addSql('ALTER TABLE stu_spacecraft ALTER direction DROP NOT NULL');
        $this->addSql('UPDATE stu_spacecraft SET direction = null WHERE direction = 0');
        $this->addSql('ALTER TABLE stu_buildplans RENAME TO stu_buildplan');

        $this->addSql('ALTER INDEX idx_8e777ae9afc2b591 RENAME TO IDX_2AD626BCAFC2B591');
        $this->addSql('ALTER INDEX idx_8e777ae91c6af6fd RENAME TO IDX_2AD626BC1C6AF6FD');
        $this->addSql('ALTER INDEX idx_e6e3cb1b2ee98d4c RENAME TO IDX_8FFD6A1A2EE98D4C');
        $this->addSql('ALTER INDEX idx_e6e3cb1ba76ed395 RENAME TO IDX_8FFD6A1AA76ED395');
        $this->addSql('ALTER INDEX idx_3d7ad378b4acc212 RENAME TO IDX_AD2CDF30B4ACC212');
        $this->addSql('ALTER INDEX idx_3d7ad378f0aa09db RENAME TO IDX_AD2CDF30F0AA09DB');
        $this->addSql('ALTER INDEX ship_system_status_idx RENAME TO spacecraft_system_status_idx');
        $this->addSql('ALTER INDEX ship_system_type_idx RENAME TO spacecraft_system_type_idx');
        $this->addSql('ALTER INDEX ship_system_mode_idx RENAME TO spacecraft_system_mode_idx');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C782E0C3915ABAF6 ON stu_station (influence_area_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_spacecraft_system RENAME TO stu_ship_system');
        $this->addSql('ALTER TABLE stu_rump ALTER base_warpdrive DROP NOT NULL');
        $this->addSql('ALTER TABLE stu_rump RENAME TO stu_rumps');
        $this->addSql('UPDATE stu_spacecraft SET direction = 0 WHERE direction is null');
        $this->addSql('ALTER TABLE stu_spacecraft ALTER direction SET NOT NULL');
        $this->addSql('ALTER TABLE stu_buildplan RENAME TO stu_buildplans');

        $this->addSql('ALTER INDEX idx_2ad626bc1c6af6fd RENAME TO idx_8e777ae91c6af6fd');
        $this->addSql('ALTER INDEX spacecraft_system_type_idx RENAME TO ship_system_type_idx');
        $this->addSql('ALTER INDEX spacecraft_system_status_idx RENAME TO ship_system_status_idx');
        $this->addSql('ALTER INDEX idx_2ad626bcafc2b591 RENAME TO idx_8e777ae9afc2b591');
        $this->addSql('ALTER INDEX spacecraft_system_mode_idx RENAME TO ship_system_mode_idx');
        $this->addSql('ALTER INDEX idx_ad2cdf30f0aa09db RENAME TO idx_3d7ad378f0aa09db');
        $this->addSql('ALTER INDEX idx_ad2cdf30b4acc212 RENAME TO idx_3d7ad378b4acc212');
        $this->addSql('ALTER INDEX idx_8ffd6a1aa76ed395 RENAME TO idx_e6e3cb1ba76ed395');
        $this->addSql('ALTER INDEX idx_8ffd6a1a2ee98d4c RENAME TO idx_e6e3cb1b2ee98d4c');
        $this->addSql('DROP INDEX UNIQ_C782E0C3915ABAF6');
    }
}
