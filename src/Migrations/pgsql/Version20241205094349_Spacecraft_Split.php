<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241205094349_Spacecraft_Split extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Split ship entity into ships and stations with abstract spacecraft entity.';
    }

    public function up(Schema $schema): void
    {
        // CREATE NEW TABLES
        $this->createSpacecraft();
        $this->createShip();
        $this->createStation();
        $this->createTrumfield();

        // MOVE DATA
        $this->fillSpacecraft();
        $this->fillShip();
        $this->fillStation();
        $this->fillTrumfield();
        $this->extendExistingData();

        // ADD CONSTRAINTS
        $this->addConstraints();

        // REFACTORING
        $this->addSql('ALTER TABLE stu_colonies_shipqueue DROP CONSTRAINT FK_BEDCCA2FC256317D');
        $this->addSql('ALTER TABLE stu_colonies_shipqueue ADD CONSTRAINT FK_BEDCCA2FC256317D FOREIGN KEY (ship_id) REFERENCES stu_ship (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE stu_colonies_shiprepair DROP CONSTRAINT FK_F14F182FC256317D');
        $this->addSql('ALTER TABLE stu_colonies_shiprepair ADD CONSTRAINT FK_F14F182FC256317D FOREIGN KEY (ship_id) REFERENCES stu_ship (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE stu_construction_progress DROP CONSTRAINT fk_57d2ad04c256317d');
        $this->addSql('DROP INDEX uniq_57d2ad04c256317d');
        $this->addSql('DROP INDEX construction_progress_ship_idx');
        $this->addSql('ALTER TABLE stu_construction_progress RENAME COLUMN ship_id TO station_id');
        $this->addSql('ALTER TABLE stu_construction_progress ADD CONSTRAINT FK_57D2AD0421BDB235 FOREIGN KEY (station_id) REFERENCES stu_station (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_57D2AD0421BDB235 ON stu_construction_progress (station_id)');
        $this->addSql('ALTER TABLE stu_crew_assign DROP CONSTRAINT fk_2ca6e80ac907e695');
        $this->addSql('DROP INDEX ship_crew_ship_idx');
        $this->addSql('ALTER TABLE stu_crew_assign RENAME COLUMN ship_id TO spacecraft_id');
        $this->addSql('ALTER TABLE stu_crew_assign ADD CONSTRAINT FK_4793ED241C6AF6FD FOREIGN KEY (spacecraft_id) REFERENCES stu_spacecraft (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX ship_crew_spacecraft_idx ON stu_crew_assign (spacecraft_id)');
        $this->addSql('ALTER TABLE stu_dockingrights DROP CONSTRAINT fk_e7d4b2ac907e695');
        $this->addSql('DROP INDEX dockingrights_ship_idx');
        $this->addSql('ALTER TABLE stu_dockingrights RENAME COLUMN ships_id TO station_id');
        $this->addSql('ALTER TABLE stu_dockingrights ADD CONSTRAINT FK_E7D4B2A21BDB235 FOREIGN KEY (station_id) REFERENCES stu_station (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX dockingrights_station_idx ON stu_dockingrights (station_id)');
        $this->addSql('ALTER TABLE stu_fleets DROP CONSTRAINT FK_2042261BC907E695');
        $this->addSql('ALTER TABLE stu_fleets ADD CONSTRAINT FK_2042261BC907E695 FOREIGN KEY (ships_id) REFERENCES stu_ship (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE stu_mining_queue DROP CONSTRAINT FK_BBFEF8C4C256317D');
        $this->addSql('ALTER TABLE stu_mining_queue ADD CONSTRAINT FK_BBFEF8C4C256317D FOREIGN KEY (ship_id) REFERENCES stu_ship (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE stu_repair_task DROP CONSTRAINT FK_36DA3BAFC256317D');
        $this->addSql('DROP INDEX idx_36da3bafc256317d');
        $this->addSql('ALTER TABLE stu_repair_task RENAME COLUMN ship_id TO spacecraft_id');
        $this->addSql('ALTER TABLE stu_repair_task ADD CONSTRAINT FK_36DA3BAFC256317D FOREIGN KEY (spacecraft_id) REFERENCES stu_spacecraft (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_36DA3BAF1C6AF6FD ON stu_repair_task (spacecraft_id)');
        $this->addSql('ALTER TABLE stu_rumps_categories DROP points');
        $this->addSql('ALTER TABLE stu_ship_log DROP CONSTRAINT fk_74cef0eec256317d');
        $this->addSql('DROP INDEX ship_log_ship_idx');
        $this->addSql('ALTER TABLE stu_ship_log RENAME COLUMN ship_id TO spacecraft_id');
        $this->addSql('ALTER TABLE stu_ship_log ADD CONSTRAINT FK_74CEF0EE1C6AF6FD FOREIGN KEY (spacecraft_id) REFERENCES stu_spacecraft (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_74CEF0EE1C6AF6FD ON stu_ship_log (spacecraft_id)');
        $this->addSql('ALTER TABLE stu_ship_system DROP CONSTRAINT fk_fc8bbeb7c907e695');
        $this->addSql('DROP INDEX ship_system_ship_idx');
        $this->addSql('ALTER TABLE stu_ship_system RENAME COLUMN ship_id TO spacecraft_id');
        $this->addSql('ALTER TABLE stu_ship_system ADD CONSTRAINT FK_8E777AE91C6AF6FD FOREIGN KEY (spacecraft_id) REFERENCES stu_spacecraft (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_8E777AE91C6AF6FD ON stu_ship_system (spacecraft_id)');
        $this->addSql('ALTER INDEX ship_system_module_idx RENAME TO IDX_8E777AE9AFC2B591');
        $this->addSql('ALTER TABLE stu_ship_takeover DROP CONSTRAINT fk_4b0b8a7c8b898915');
        $this->addSql('ALTER TABLE stu_ship_takeover DROP CONSTRAINT fk_4b0b8a7c93e8816');
        $this->addSql('DROP INDEX uniq_4b0b8a7c93e8816');
        $this->addSql('DROP INDEX uniq_4b0b8a7c8b898915');
        $this->addSql('DROP INDEX ship_takeover_target_idx');
        $this->addSql('DROP INDEX ship_takeover_source_idx');
        $this->addSql('ALTER TABLE stu_ship_takeover RENAME COLUMN source_ship_id TO source_spacecraft_id');
        $this->addSql('ALTER TABLE stu_ship_takeover RENAME COLUMN target_ship_id TO target_spacecraft_id');
        $this->addSql('ALTER TABLE stu_ship_takeover ADD CONSTRAINT FK_4B0B8A7CD906279F FOREIGN KEY (source_spacecraft_id) REFERENCES stu_spacecraft (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE stu_ship_takeover ADD CONSTRAINT FK_4B0B8A7CE6C54C FOREIGN KEY (target_spacecraft_id) REFERENCES stu_spacecraft (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4B0B8A7CD906279F ON stu_ship_takeover (source_spacecraft_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4B0B8A7CE6C54C ON stu_ship_takeover (target_spacecraft_id)');
        $this->addSql('CREATE INDEX ship_takeover_target_idx ON stu_ship_takeover (target_spacecraft_id)');
        $this->addSql('CREATE INDEX ship_takeover_source_idx ON stu_ship_takeover (source_spacecraft_id)');
        $this->addSql('ALTER TABLE stu_shipyard_shipqueue DROP CONSTRAINT fk_7c6ffb42c256317d');
        $this->addSql('DROP INDEX idx_7c6ffb42c256317d');
        $this->addSql('ALTER TABLE stu_shipyard_shipqueue RENAME COLUMN ship_id TO station_id');
        $this->addSql('ALTER TABLE stu_shipyard_shipqueue ADD CONSTRAINT FK_7C6FFB4221BDB235 FOREIGN KEY (station_id) REFERENCES stu_station (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_7C6FFB4221BDB235 ON stu_shipyard_shipqueue (station_id)');
        $this->addSql('ALTER TABLE stu_spacecraft_emergency DROP CONSTRAINT FK_F0230813C256317D');
        $this->addSql('DROP INDEX spacecraft_emergency_ship_idx');
        $this->addSql('ALTER TABLE stu_spacecraft_emergency RENAME COLUMN ship_id TO spacecraft_id');
        $this->addSql('ALTER TABLE stu_spacecraft_emergency ADD CONSTRAINT FK_F0230813C256317D FOREIGN KEY (spacecraft_id) REFERENCES stu_spacecraft (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_F02308131C6AF6FD ON stu_spacecraft_emergency (spacecraft_id)');
        $this->addSql('ALTER TABLE stu_station_shiprepair DROP CONSTRAINT FK_51875AF721BDB235');
        $this->addSql('ALTER TABLE stu_station_shiprepair DROP CONSTRAINT FK_51875AF7C256317D');
        $this->addSql('ALTER TABLE stu_station_shiprepair ADD CONSTRAINT FK_51875AF721BDB235 FOREIGN KEY (station_id) REFERENCES stu_station (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE stu_station_shiprepair ADD CONSTRAINT FK_51875AF7C256317D FOREIGN KEY (ship_id) REFERENCES stu_ship (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE stu_storage DROP CONSTRAINT FK_CC10346C256317D');
        $this->addSql('DROP INDEX storage_ship_idx');
        $this->addSql('ALTER TABLE stu_storage RENAME COLUMN ship_id TO spacecraft_id');
        $this->addSql('ALTER TABLE stu_storage ADD trumfield_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE stu_storage ADD CONSTRAINT FK_CC10346668E6720 FOREIGN KEY (trumfield_id) REFERENCES stu_trumfield (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE stu_storage ADD CONSTRAINT FK_CC103461C6AF6FD FOREIGN KEY (spacecraft_id) REFERENCES stu_spacecraft (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_CC10346668E6720 ON stu_storage (trumfield_id)');
        $this->addSql('CREATE INDEX storage_spacecraft_idx ON stu_storage (spacecraft_id)');
        $this->addSql('ALTER TABLE stu_tholian_web DROP CONSTRAINT FK_D032F9A0C256317D');
        $this->addSql('ALTER TABLE stu_tholian_web ADD CONSTRAINT FK_D032F9A0C256317D FOREIGN KEY (ship_id) REFERENCES stu_ship (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE stu_torpedo_storage DROP CONSTRAINT fk_82371911c256317d');
        $this->addSql('DROP INDEX uniq_82371911c256317d');
        $this->addSql('DROP INDEX torpedo_storage_ship_idx');
        $this->addSql('ALTER TABLE stu_torpedo_storage RENAME COLUMN ship_id TO spacecraft_id');
        $this->addSql('ALTER TABLE stu_torpedo_storage ADD CONSTRAINT FK_823719111C6AF6FD FOREIGN KEY (spacecraft_id) REFERENCES stu_spacecraft (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_823719111C6AF6FD ON stu_torpedo_storage (spacecraft_id)');
        $this->addSql('CREATE INDEX torpedo_storage_spacecraft_idx ON stu_torpedo_storage (spacecraft_id)');
        $this->addSql('ALTER TABLE stu_trade_posts DROP CONSTRAINT fk_13d25e73c256317d');
        $this->addSql('DROP INDEX uniq_13d25e73c256317d');
        $this->addSql('DROP INDEX trade_post_ship_idx');
        $this->addSql('ALTER TABLE stu_trade_posts RENAME COLUMN ship_id TO station_id');
        $this->addSql('ALTER TABLE stu_trade_posts ADD CONSTRAINT FK_13D25E7321BDB235 FOREIGN KEY (station_id) REFERENCES stu_station (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_13D25E7321BDB235 ON stu_trade_posts (station_id)');
        $this->addSql('CREATE INDEX trade_post_station_idx ON stu_trade_posts (station_id)');

        // DROP OLD TABLES
        $this->dropStuShips();
    }

    private function createSpacecraft(): void
    {
        $this->addSql('CREATE TABLE stu_spacecraft (id INT GENERATED BY DEFAULT AS IDENTITY NOT NULL, user_id INT NOT NULL, rump_id INT NOT NULL, plan_id INT DEFAULT NULL, direction SMALLINT NOT NULL, name VARCHAR(255) NOT NULL, alvl SMALLINT NOT NULL, lss_mode SMALLINT NOT NULL, huelle INT NOT NULL, max_huelle INT NOT NULL, schilde INT NOT NULL, max_schilde INT NOT NULL, tractored_ship_id INT DEFAULT NULL, holding_web_id INT DEFAULT NULL, database_id INT DEFAULT NULL, disabled BOOLEAN NOT NULL, hit_chance SMALLINT NOT NULL, evade_chance SMALLINT NOT NULL, base_damage SMALLINT NOT NULL, sensor_range SMALLINT NOT NULL, shield_regeneration_timer INT NOT NULL, state SMALLINT NOT NULL, location_id INT NOT NULL, in_emergency BOOLEAN NOT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4BD20E2EEE54A42E ON stu_spacecraft (tractored_ship_id)');
        $this->addSql('CREATE INDEX IDX_4BD20E2E73D3801E ON stu_spacecraft (holding_web_id)');
        $this->addSql('CREATE INDEX IDX_4BD20E2EA76ED395 ON stu_spacecraft (user_id)');
        $this->addSql('CREATE INDEX IDX_4BD20E2E2EE98D4C ON stu_spacecraft (rump_id)');
        $this->addSql('CREATE INDEX IDX_4BD20E2EE899029B ON stu_spacecraft (plan_id)');
        $this->addSql('CREATE INDEX IDX_4BD20E2E64D218E ON stu_spacecraft (location_id)');
    }

    private function createShip(): void
    {
        $this->addSql('CREATE TABLE stu_ship (id INT NOT NULL, fleet_id INT DEFAULT NULL, docked_to_id INT DEFAULT NULL, is_fleet_leader BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_65024D724B061DF9 ON stu_ship (fleet_id)');
        $this->addSql('CREATE INDEX IDX_65024D729B76929F ON stu_ship (docked_to_id)');
    }

    private function createStation(): void
    {
        $this->addSql('CREATE TABLE stu_station (id INT NOT NULL, influence_area_id INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX station_influence_area_idx ON stu_station (influence_area_id)');
    }

    private function createTrumfield(): void
    {
        $this->addSql('CREATE TABLE stu_trumfield (id INT GENERATED BY DEFAULT AS IDENTITY NOT NULL, huelle INT NOT NULL, former_rump_id INT NOT NULL, location_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3CBB9A4E64D218E ON stu_trumfield (location_id)');
        $this->addSql('ALTER TABLE stu_trumfield ADD CONSTRAINT FK_3CBB9A4E64D218E FOREIGN KEY (location_id) REFERENCES stu_location (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    private function fillSpacecraft(): void
    {
        $this->addSql('INSERT INTO stu_spacecraft
                    (id, user_id, rump_id, plan_id,direction, name, alvl, lss_mode, huelle, max_huelle, schilde, max_schilde, tractored_ship_id, holding_web_id, database_id, disabled,
                    hit_chance, evade_chance, base_damage, sensor_range, shield_regeneration_timer, state, location_id, in_emergency, type)
                    SELECT id, user_id, rumps_id, plans_id, direction, name, alvl, lss_mode, huelle, max_huelle, schilde, max_schilde, tractored_ship_id, holding_web_id, database_id, disabled,
                    hit_chance, evade_chance, base_damage, sensor_range, shield_regeneration_timer, state, location_id, in_emergency, (CASE WHEN type = 0 THEN \'SHIP\' ELSE \'STATION\' END)
                    FROM stu_ships 
                    WHERE type in (0,1)');
    }

    private function fillShip(): void
    {
        $this->addSql('INSERT INTO stu_ship
                    (id, fleet_id, docked_to_id, is_fleet_leader)
                    SELECT id, fleets_id, dock, is_fleet_leader 
                    FROM stu_ships 
                    WHERE type = 0');
    }
    private function fillStation(): void
    {
        $this->addSql('INSERT INTO stu_station
                    (id, influence_area_id)
                    SELECT id, influence_area_id
                    FROM stu_ships 
                    WHERE type = 1');
    }
    private function fillTrumfield(): void
    {
        $this->addSql('DELETE FROM stu_construction_progress cp
                    WHERE EXISTS (SELECT * FROM stu_ships s
                                    WHERE cp.ship_id = s.id
                                    AND s.type = 2)');
        $this->addSql('DELETE FROM stu_dockingrights dr
                    WHERE EXISTS (SELECT * FROM stu_ships s
                                    WHERE dr.ships_id = s.id
                                    AND s.type = 2)');
        $this->addSql('DELETE FROM stu_spacecraft_emergency se
                    WHERE EXISTS (SELECT * FROM stu_ships s
                                    WHERE se.ship_id = s.id
                                    AND s.type = 2)');
        $this->addSql('INSERT INTO stu_trumfield
                    (huelle, former_rump_id, location_id)
                    SELECT huelle, former_rumps_id, location_id
                    FROM stu_ships
                    WHERE type = 2');
        $this->addSql('DELETE FROM stu_storage st
                    WHERE EXISTS (SELECT * FROM stu_ships s
                                WHERE s.id = st.ship_id
                                AND s.type = 2)');
    }

    private function extendExistingData(): void
    {
        $this->addSql('ALTER TABLE stu_rumps_categories ADD type VARCHAR(255) DEFAULT NULL');
        $this->addSql('UPDATE stu_rumps_categories SET type = \'STATION\' WHERE ID IN (11,12)');
        $this->addSql('UPDATE stu_rumps_categories SET type = \'SHIP\' WHERE type IS NULL');
        $this->addSql('ALTER TABLE stu_rumps_categories ALTER type SET NOT NULL');
    }

    private function addConstraints(): void
    {
        //SPACECRAFT
        $this->addSql('ALTER TABLE stu_spacecraft ADD CONSTRAINT FK_4BD20E2EEE54A42E FOREIGN KEY (tractored_ship_id) REFERENCES stu_ship (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE stu_spacecraft ADD CONSTRAINT FK_4BD20E2E73D3801E FOREIGN KEY (holding_web_id) REFERENCES stu_tholian_web (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE stu_spacecraft ADD CONSTRAINT FK_4BD20E2EA76ED395 FOREIGN KEY (user_id) REFERENCES stu_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE stu_spacecraft ADD CONSTRAINT FK_4BD20E2E2EE98D4C FOREIGN KEY (rump_id) REFERENCES stu_rumps (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE stu_spacecraft ADD CONSTRAINT FK_4BD20E2EE899029B FOREIGN KEY (plan_id) REFERENCES stu_buildplans (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE stu_spacecraft ADD CONSTRAINT FK_4BD20E2E64D218E FOREIGN KEY (location_id) REFERENCES stu_location (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        //SHIP
        $this->addSql('ALTER TABLE stu_ship ADD CONSTRAINT FK_65024D724B061DF9 FOREIGN KEY (fleet_id) REFERENCES stu_fleets (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE stu_ship ADD CONSTRAINT FK_65024D729B76929F FOREIGN KEY (docked_to_id) REFERENCES stu_station (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE stu_ship ADD CONSTRAINT FK_65024D72BF396750 FOREIGN KEY (id) REFERENCES stu_spacecraft (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        //STATION
        $this->addSql('ALTER TABLE stu_station ADD CONSTRAINT FK_C782E0C3915ABAF6 FOREIGN KEY (influence_area_id) REFERENCES stu_systems (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE stu_station ADD CONSTRAINT FK_C782E0C3BF396750 FOREIGN KEY (id) REFERENCES stu_spacecraft (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    private function dropStuShips(): void
    {
        $this->addSql('ALTER TABLE stu_ships DROP CONSTRAINT fk_a560dd56235bf180');
        $this->addSql('ALTER TABLE stu_ships DROP CONSTRAINT fk_a560dd56423bb3e1');
        $this->addSql('ALTER TABLE stu_ships DROP CONSTRAINT fk_a560dd5664d218e');
        $this->addSql('ALTER TABLE stu_ships DROP CONSTRAINT fk_a560dd5673d3801e');
        $this->addSql('ALTER TABLE stu_ships DROP CONSTRAINT fk_a560dd5680446eeb');
        $this->addSql('ALTER TABLE stu_ships DROP CONSTRAINT fk_a560dd56915abaf6');
        $this->addSql('ALTER TABLE stu_ships DROP CONSTRAINT fk_a560dd5698355913');
        $this->addSql('ALTER TABLE stu_ships DROP CONSTRAINT fk_a560dd56a76ed395');
        $this->addSql('ALTER TABLE stu_ships DROP CONSTRAINT fk_a560dd56ee54a42e');
        $this->addSql('ALTER TABLE stu_ships DROP CONSTRAINT fk_a560dd56f0aa09db');
        $this->addSql('DROP TABLE stu_ships');
    }

    public function down(Schema $schema): void
    {
        // Sorry, but no way back!
    }
}
