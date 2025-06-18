<?php

declare(strict_types=1);

namespace Stu\Migrations\Sqlite;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250618185937 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_alliance_boards (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, alliance_id INTEGER NOT NULL, name VARCHAR(255) NOT NULL, CONSTRAINT FK_5D868E4710A0EA3F FOREIGN KEY (alliance_id) REFERENCES stu_alliances (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX alliance_idx ON stu_alliance_boards (alliance_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_alliance_posts (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, topic_id INTEGER NOT NULL, board_id INTEGER NOT NULL, name VARCHAR(255) NOT NULL, date INTEGER NOT NULL, text CLOB NOT NULL, user_id INTEGER NOT NULL, lastedit INTEGER DEFAULT NULL, CONSTRAINT FK_31F4A4121F55203D FOREIGN KEY (topic_id) REFERENCES stu_alliance_topics (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_31F4A412E7EC5785 FOREIGN KEY (board_id) REFERENCES stu_alliance_boards (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_31F4A412A76ED395 FOREIGN KEY (user_id) REFERENCES stu_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_31F4A4121F55203D ON stu_alliance_posts (topic_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_31F4A412E7EC5785 ON stu_alliance_posts (board_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_31F4A412A76ED395 ON stu_alliance_posts (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX topic_date_idx ON stu_alliance_posts (topic_id, date)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX board_date_idx ON stu_alliance_posts (board_id, date)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_alliance_settings (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, alliance_id INTEGER NOT NULL, setting VARCHAR(255) NOT NULL, value VARCHAR(255) NOT NULL, CONSTRAINT FK_39FF05F710A0EA3F FOREIGN KEY (alliance_id) REFERENCES stu_alliances (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_39FF05F710A0EA3F ON stu_alliance_settings (alliance_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_alliance_topics (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, board_id INTEGER NOT NULL, alliance_id INTEGER NOT NULL, name VARCHAR(255) NOT NULL, last_post_date INTEGER NOT NULL, user_id INTEGER NOT NULL, sticky BOOLEAN NOT NULL, CONSTRAINT FK_3F9E856DE7EC5785 FOREIGN KEY (board_id) REFERENCES stu_alliance_boards (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_3F9E856D10A0EA3F FOREIGN KEY (alliance_id) REFERENCES stu_alliances (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_3F9E856DA76ED395 FOREIGN KEY (user_id) REFERENCES stu_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3F9E856DE7EC5785 ON stu_alliance_topics (board_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3F9E856D10A0EA3F ON stu_alliance_topics (alliance_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3F9E856DA76ED395 ON stu_alliance_topics (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX recent_topics_idx ON stu_alliance_topics (alliance_id, last_post_date)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX ordered_topics_idx ON stu_alliance_topics (board_id, last_post_date)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_alliances (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description CLOB NOT NULL, homepage VARCHAR(255) NOT NULL, date INTEGER NOT NULL, faction_id INTEGER DEFAULT NULL, accept_applications BOOLEAN NOT NULL, avatar VARCHAR(32) NOT NULL, rgb_code VARCHAR(7) NOT NULL, CONSTRAINT FK_A36183F74448F8DA FOREIGN KEY (faction_id) REFERENCES stu_factions (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_A36183F74448F8DA ON stu_alliances (faction_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_alliances_jobs (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, alliance_id INTEGER NOT NULL, user_id INTEGER NOT NULL, type SMALLINT NOT NULL, CONSTRAINT FK_3C71C67B10A0EA3F FOREIGN KEY (alliance_id) REFERENCES stu_alliances (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_3C71C67BA76ED395 FOREIGN KEY (user_id) REFERENCES stu_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3C71C67B10A0EA3F ON stu_alliances_jobs (alliance_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3C71C67BA76ED395 ON stu_alliances_jobs (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_alliances_relations (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, type SMALLINT NOT NULL, alliance_id INTEGER NOT NULL, recipient INTEGER NOT NULL, date INTEGER NOT NULL, CONSTRAINT FK_9EBADCD910A0EA3F FOREIGN KEY (alliance_id) REFERENCES stu_alliances (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_9EBADCD96804FB49 FOREIGN KEY (recipient) REFERENCES stu_alliances (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_9EBADCD910A0EA3F ON stu_alliances_relations (alliance_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_9EBADCD96804FB49 ON stu_alliances_relations (recipient)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX alliance_relation_idx ON stu_alliances_relations (alliance_id, recipient)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_anomaly (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, remaining_ticks INTEGER NOT NULL, anomaly_type_id INTEGER NOT NULL, location_id INTEGER DEFAULT NULL, parent_id INTEGER DEFAULT NULL, data CLOB DEFAULT NULL, CONSTRAINT FK_A1426D1126894FC7 FOREIGN KEY (anomaly_type_id) REFERENCES stu_anomaly_type (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_A1426D1164D218E FOREIGN KEY (location_id) REFERENCES stu_location (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_A1426D11727ACA70 FOREIGN KEY (parent_id) REFERENCES stu_anomaly (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_A1426D1164D218E ON stu_anomaly (location_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_A1426D11727ACA70 ON stu_anomaly (parent_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX anomaly_to_type_idx ON stu_anomaly (anomaly_type_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX anomaly_remaining_idx ON stu_anomaly (remaining_ticks)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_anomaly_type (id INTEGER NOT NULL, name VARCHAR(200) NOT NULL, lifespan_in_ticks INTEGER NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_astro_entry (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, state SMALLINT NOT NULL, astro_start_turn INTEGER DEFAULT NULL, systems_id INTEGER DEFAULT NULL, region_id INTEGER DEFAULT NULL, field_ids CLOB NOT NULL, CONSTRAINT FK_783E1A92A76ED395 FOREIGN KEY (user_id) REFERENCES stu_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_783E1A92411D7F6D FOREIGN KEY (systems_id) REFERENCES stu_systems (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_783E1A9298260155 FOREIGN KEY (region_id) REFERENCES stu_map_regions (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX astro_entry_user_idx ON stu_astro_entry (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX astro_entry_star_system_idx ON stu_astro_entry (systems_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX astro_entry_map_region_idx ON stu_astro_entry (region_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_auction_bid (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, auction_id INTEGER NOT NULL, user_id INTEGER NOT NULL, max_amount INTEGER NOT NULL, CONSTRAINT FK_F198A82957B8F0DE FOREIGN KEY (auction_id) REFERENCES stu_deals (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_F198A829A76ED395 FOREIGN KEY (user_id) REFERENCES stu_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_F198A82957B8F0DE ON stu_auction_bid (auction_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_F198A829A76ED395 ON stu_auction_bid (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX auction_bid_sort_idx ON stu_auction_bid (max_amount)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_award (id INTEGER NOT NULL, prestige INTEGER NOT NULL, description CLOB NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_basic_trade (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, faction_id INTEGER DEFAULT NULL, commodity_id INTEGER NOT NULL, buy_sell SMALLINT NOT NULL, value INTEGER NOT NULL, date_ms BIGINT DEFAULT NULL, uniqid VARCHAR(255) NOT NULL, user_id INTEGER DEFAULT NULL, CONSTRAINT FK_15ACD9904448F8DA FOREIGN KEY (faction_id) REFERENCES stu_factions (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_15ACD990B4ACC212 FOREIGN KEY (commodity_id) REFERENCES stu_commodity (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_15ACD9904448F8DA ON stu_basic_trade (faction_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_15ACD990B4ACC212 ON stu_basic_trade (commodity_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX base_trade_idx ON stu_basic_trade (faction_id, commodity_id, date_ms)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_blocked_user (user_id INTEGER NOT NULL, time INTEGER NOT NULL, email_hash VARCHAR(255) NOT NULL, mobile_hash VARCHAR(255) DEFAULT NULL, PRIMARY KEY(user_id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_buildings (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, lager SMALLINT NOT NULL, eps SMALLINT NOT NULL, eps_cost SMALLINT NOT NULL, eps_proc SMALLINT NOT NULL, bev_pro SMALLINT NOT NULL, bev_use SMALLINT NOT NULL, integrity SMALLINT NOT NULL, research_id INTEGER NOT NULL, "view" BOOLEAN NOT NULL, buildtime INTEGER NOT NULL, blimit SMALLINT NOT NULL, bclimit SMALLINT NOT NULL, is_activateable BOOLEAN NOT NULL, bm_col SMALLINT NOT NULL)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX eps_production_idx ON stu_buildings (eps_proc)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX buildmenu_column_idx ON stu_buildings (bm_col)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX building_research_idx ON stu_buildings (research_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_buildings_commodity (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, buildings_id INTEGER NOT NULL, commodity_id INTEGER NOT NULL, count INTEGER NOT NULL, CONSTRAINT FK_D20755B9B4ACC212 FOREIGN KEY (commodity_id) REFERENCES stu_commodity (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_D20755B91485E613 FOREIGN KEY (buildings_id) REFERENCES stu_buildings (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_D20755B9B4ACC212 ON stu_buildings_commodity (commodity_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX building_commodity_building_idx ON stu_buildings_commodity (buildings_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX commodity_count_idx ON stu_buildings_commodity (commodity_id, count)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_buildings_cost (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, buildings_id INTEGER NOT NULL, commodity_id INTEGER NOT NULL, count INTEGER NOT NULL, CONSTRAINT FK_A411CF8CB4ACC212 FOREIGN KEY (commodity_id) REFERENCES stu_commodity (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_A411CF8C1485E613 FOREIGN KEY (buildings_id) REFERENCES stu_buildings (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_A411CF8CB4ACC212 ON stu_buildings_cost (commodity_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX building_cost_building_idx ON stu_buildings_cost (buildings_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_buildings_field_alternative (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, fieldtype INTEGER NOT NULL, buildings_id INTEGER NOT NULL, alternate_buildings_id INTEGER NOT NULL, research_id INTEGER DEFAULT NULL, CONSTRAINT FK_A9CE59831485E613 FOREIGN KEY (buildings_id) REFERENCES stu_buildings (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_A9CE5983D17B76D1 FOREIGN KEY (alternate_buildings_id) REFERENCES stu_buildings (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_A9CE59831485E613 ON stu_buildings_field_alternative (buildings_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_A9CE5983D17B76D1 ON stu_buildings_field_alternative (alternate_buildings_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX building_field_idx ON stu_buildings_field_alternative (fieldtype, buildings_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_buildings_functions (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, buildings_id INTEGER NOT NULL, function SMALLINT NOT NULL, CONSTRAINT FK_CF2657C11485E613 FOREIGN KEY (buildings_id) REFERENCES stu_buildings (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX building_function_building_idx ON stu_buildings_functions (buildings_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX building_function_function_idx ON stu_buildings_functions (function)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_buildings_upgrades (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, upgrade_from INTEGER NOT NULL, upgrade_to INTEGER NOT NULL, research_id INTEGER NOT NULL, description VARCHAR(255) NOT NULL, energy_cost INTEGER NOT NULL, CONSTRAINT FK_BFDD6E5BF0CC2E2A FOREIGN KEY (upgrade_to) REFERENCES stu_buildings (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_BFDD6E5BCDECE66E FOREIGN KEY (upgrade_from) REFERENCES stu_buildings (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_BFDD6E5BF0CC2E2A ON stu_buildings_upgrades (upgrade_to)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_BFDD6E5BCDECE66E ON stu_buildings_upgrades (upgrade_from)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX upgrade_from_research_idx ON stu_buildings_upgrades (upgrade_from, research_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_buildings_upgrades_cost (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, buildings_upgrades_id BIGINT NOT NULL, commodity_id INTEGER NOT NULL, amount INTEGER NOT NULL, CONSTRAINT FK_37358E19B4ACC212 FOREIGN KEY (commodity_id) REFERENCES stu_commodity (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_37358E1927B02F4A FOREIGN KEY (buildings_upgrades_id) REFERENCES stu_buildings_upgrades (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_37358E19B4ACC212 ON stu_buildings_upgrades_cost (commodity_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX buildings_upgrades_idx ON stu_buildings_upgrades_cost (buildings_upgrades_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_buildplan (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, rump_id INTEGER NOT NULL, user_id INTEGER NOT NULL, name VARCHAR(255) NOT NULL, buildtime INTEGER NOT NULL, signature VARCHAR(32) DEFAULT NULL, crew SMALLINT NOT NULL, CONSTRAINT FK_8FFD6A1A2EE98D4C FOREIGN KEY (rump_id) REFERENCES stu_rump (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_8FFD6A1AA76ED395 FOREIGN KEY (user_id) REFERENCES stu_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_8FFD6A1A2EE98D4C ON stu_buildplan (rump_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_8FFD6A1AA76ED395 ON stu_buildplan (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX buildplan_signatures_idx ON stu_buildplan (user_id, rump_id, signature)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_buildplans_hangar (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, rump_id INTEGER NOT NULL, buildplan_id INTEGER NOT NULL, default_torpedo_type_id INTEGER DEFAULT NULL, start_energy_costs INTEGER NOT NULL, CONSTRAINT FK_DE1E0721497D0592 FOREIGN KEY (default_torpedo_type_id) REFERENCES stu_torpedo_types (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_DE1E07218638E4E7 FOREIGN KEY (buildplan_id) REFERENCES stu_buildplan (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_DE1E07212EE98D4C FOREIGN KEY (rump_id) REFERENCES stu_rump (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_DE1E0721497D0592 ON stu_buildplans_hangar (default_torpedo_type_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_DE1E07218638E4E7 ON stu_buildplans_hangar (buildplan_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX rump_idx ON stu_buildplans_hangar (rump_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_buildplans_modules (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, buildplan_id INTEGER NOT NULL, module_type SMALLINT NOT NULL, module_id INTEGER NOT NULL, module_special INTEGER DEFAULT NULL, module_count SMALLINT NOT NULL, CONSTRAINT FK_82701FF58638E4E7 FOREIGN KEY (buildplan_id) REFERENCES stu_buildplan (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_82701FF5AFC2B591 FOREIGN KEY (module_id) REFERENCES stu_modules (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_82701FF58638E4E7 ON stu_buildplans_modules (buildplan_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_82701FF5AFC2B591 ON stu_buildplans_modules (module_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX buildplan_module_type_idx ON stu_buildplans_modules (buildplan_id, module_type, module_special)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_buoy (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, text CLOB NOT NULL, location_id INTEGER NOT NULL, CONSTRAINT FK_B6DF528964D218E FOREIGN KEY (location_id) REFERENCES stu_location (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_B6DF5289A76ED395 FOREIGN KEY (user_id) REFERENCES stu_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_B6DF528964D218E ON stu_buoy (location_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_B6DF5289A76ED395 ON stu_buoy (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_colonies (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, colonies_classes_id INTEGER NOT NULL, user_id INTEGER NOT NULL, starsystem_map_id INTEGER NOT NULL, name VARCHAR(255) NOT NULL, planet_name VARCHAR(100) NOT NULL, bev_work INTEGER NOT NULL, bev_free INTEGER NOT NULL, bev_max INTEGER NOT NULL, eps INTEGER NOT NULL, max_eps INTEGER NOT NULL, max_storage INTEGER NOT NULL, mask CLOB DEFAULT NULL, database_id INTEGER DEFAULT NULL, populationlimit INTEGER NOT NULL, immigrationstate BOOLEAN NOT NULL, shields INTEGER DEFAULT NULL, shield_frequency INTEGER DEFAULT NULL, torpedo_type INTEGER DEFAULT NULL, rotation_factor INTEGER NOT NULL, surface_width INTEGER NOT NULL, CONSTRAINT FK_D1C60F739106126 FOREIGN KEY (colonies_classes_id) REFERENCES stu_colonies_classes (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_D1C60F73496DDE10 FOREIGN KEY (starsystem_map_id) REFERENCES stu_sys_map (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_D1C60F73A76ED395 FOREIGN KEY (user_id) REFERENCES stu_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_D1C60F73942323E3 FOREIGN KEY (torpedo_type) REFERENCES stu_torpedo_types (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_D1C60F73F0AA09DB FOREIGN KEY (database_id) REFERENCES stu_database_entrys (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_D1C60F73496DDE10 ON stu_colonies (starsystem_map_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_D1C60F73942323E3 ON stu_colonies (torpedo_type)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_D1C60F73F0AA09DB ON stu_colonies (database_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX colony_user_idx ON stu_colonies (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX colony_classes_idx ON stu_colonies (colonies_classes_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX colony_sys_map_idx ON stu_colonies (starsystem_map_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_colonies_classes (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, type INTEGER NOT NULL, database_id INTEGER DEFAULT NULL, colonizeable_fields CLOB NOT NULL, bev_growth_rate SMALLINT NOT NULL, special SMALLINT NOT NULL, allow_start BOOLEAN NOT NULL, min_rot INTEGER NOT NULL, max_rot INTEGER NOT NULL, CONSTRAINT FK_D116D262F0AA09DB FOREIGN KEY (database_id) REFERENCES stu_database_entrys (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_D116D262F0AA09DB ON stu_colonies_classes (database_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_colonies_fielddata (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, colonies_id INTEGER DEFAULT NULL, colony_sandbox_id INTEGER DEFAULT NULL, field_id SMALLINT NOT NULL, type_id INTEGER NOT NULL, buildings_id INTEGER DEFAULT NULL, terraforming_id INTEGER DEFAULT NULL, integrity SMALLINT NOT NULL, aktiv INTEGER NOT NULL, activate_after_build BOOLEAN NOT NULL, CONSTRAINT FK_7E4F10971485E613 FOREIGN KEY (buildings_id) REFERENCES stu_buildings (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_7E4F1097BD31079C FOREIGN KEY (terraforming_id) REFERENCES stu_terraforming (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_7E4F109774EB5CC8 FOREIGN KEY (colonies_id) REFERENCES stu_colonies (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_7E4F1097A0222FA4 FOREIGN KEY (colony_sandbox_id) REFERENCES stu_colony_sandbox (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_7E4F10971485E613 ON stu_colonies_fielddata (buildings_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_7E4F1097BD31079C ON stu_colonies_fielddata (terraforming_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_7E4F109774EB5CC8 ON stu_colonies_fielddata (colonies_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_7E4F1097A0222FA4 ON stu_colonies_fielddata (colony_sandbox_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX colony_field_idx ON stu_colonies_fielddata (colonies_id, field_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX sandbox_field_idx ON stu_colonies_fielddata (colony_sandbox_id, field_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX colony_building_active_idx ON stu_colonies_fielddata (colonies_id, buildings_id, aktiv)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX sandbox_building_active_idx ON stu_colonies_fielddata (colony_sandbox_id, buildings_id, aktiv)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX active_idx ON stu_colonies_fielddata (aktiv)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_colonies_shipqueue (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, colony_id INTEGER NOT NULL, user_id INTEGER NOT NULL, rump_id INTEGER NOT NULL, buildplan_id INTEGER NOT NULL, buildtime INTEGER NOT NULL, finish_date INTEGER NOT NULL, stop_date INTEGER NOT NULL, building_function_id SMALLINT NOT NULL, mode INTEGER DEFAULT NULL, ship_id INTEGER DEFAULT NULL, CONSTRAINT FK_BEDCCA2F8638E4E7 FOREIGN KEY (buildplan_id) REFERENCES stu_buildplan (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_BEDCCA2F2EE98D4C FOREIGN KEY (rump_id) REFERENCES stu_rump (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_BEDCCA2F96ADBADE FOREIGN KEY (colony_id) REFERENCES stu_colonies (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_BEDCCA2FC256317D FOREIGN KEY (ship_id) REFERENCES stu_ship (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_BEDCCA2F8638E4E7 ON stu_colonies_shipqueue (buildplan_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_BEDCCA2F2EE98D4C ON stu_colonies_shipqueue (rump_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_BEDCCA2F96ADBADE ON stu_colonies_shipqueue (colony_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_BEDCCA2FC256317D ON stu_colonies_shipqueue (ship_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX colony_shipqueue_building_function_idx ON stu_colonies_shipqueue (colony_id, building_function_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX colony_shipqueue_user_idx ON stu_colonies_shipqueue (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX colony_shipqueue_finish_date_idx ON stu_colonies_shipqueue (finish_date)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_colonies_shiprepair (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, colony_id INTEGER NOT NULL, ship_id INTEGER NOT NULL, field_id INTEGER NOT NULL, CONSTRAINT FK_F14F182F96ADBADE FOREIGN KEY (colony_id) REFERENCES stu_colonies (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_F14F182FC256317D FOREIGN KEY (ship_id) REFERENCES stu_ship (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_F14F182F96ADBADE ON stu_colonies_shiprepair (colony_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_F14F182FC256317D ON stu_colonies_shiprepair (ship_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_colonies_terraforming (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, colonies_id INTEGER NOT NULL, field_id INTEGER NOT NULL, terraforming_id INTEGER NOT NULL, finished INTEGER NOT NULL, CONSTRAINT FK_AFB8ADC7BD31079C FOREIGN KEY (terraforming_id) REFERENCES stu_terraforming (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_AFB8ADC7443707B0 FOREIGN KEY (field_id) REFERENCES stu_colonies_fielddata (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_AFB8ADC774EB5CC8 FOREIGN KEY (colonies_id) REFERENCES stu_colonies (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_AFB8ADC7BD31079C ON stu_colonies_terraforming (terraforming_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_AFB8ADC7443707B0 ON stu_colonies_terraforming (field_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX colony_idx ON stu_colonies_terraforming (colonies_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX finished_idx ON stu_colonies_terraforming (finished)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_colony_class_deposit (min_amount INTEGER NOT NULL, max_amount INTEGER NOT NULL, colony_class_id INTEGER NOT NULL, commodity_id INTEGER NOT NULL, PRIMARY KEY(colony_class_id, commodity_id), CONSTRAINT FK_A843E75CBA61CECC FOREIGN KEY (colony_class_id) REFERENCES stu_colonies_classes (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_A843E75CB4ACC212 FOREIGN KEY (commodity_id) REFERENCES stu_commodity (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_A843E75CBA61CECC ON stu_colony_class_deposit (colony_class_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_A843E75CB4ACC212 ON stu_colony_class_deposit (commodity_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_colony_class_restriction (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, colony_class_id INTEGER NOT NULL, terraforming_id INTEGER DEFAULT NULL, building_id INTEGER DEFAULT NULL, CONSTRAINT FK_CEF73D7ABA61CECC FOREIGN KEY (colony_class_id) REFERENCES stu_colonies_classes (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_CEF73D7ABD31079C FOREIGN KEY (terraforming_id) REFERENCES stu_terraforming (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_CEF73D7A4D2A7E12 FOREIGN KEY (building_id) REFERENCES stu_buildings (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_CEF73D7ABA61CECC ON stu_colony_class_restriction (colony_class_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_CEF73D7ABD31079C ON stu_colony_class_restriction (terraforming_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_CEF73D7A4D2A7E12 ON stu_colony_class_restriction (building_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_colony_deposit_mining (commodity_id INTEGER NOT NULL, amount_left INTEGER NOT NULL, user_id INTEGER NOT NULL, colony_id INTEGER NOT NULL, PRIMARY KEY(user_id, colony_id, commodity_id), CONSTRAINT FK_BD7DF1EAA76ED395 FOREIGN KEY (user_id) REFERENCES stu_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_BD7DF1EA96ADBADE FOREIGN KEY (colony_id) REFERENCES stu_colonies (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_BD7DF1EAB4ACC212 FOREIGN KEY (commodity_id) REFERENCES stu_commodity (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_BD7DF1EAA76ED395 ON stu_colony_deposit_mining (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_BD7DF1EA96ADBADE ON stu_colony_deposit_mining (colony_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_BD7DF1EAB4ACC212 ON stu_colony_deposit_mining (commodity_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_colony_fieldtype (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, field_id INTEGER NOT NULL, description VARCHAR(255) NOT NULL, normal_id INTEGER NOT NULL, category INTEGER NOT NULL)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX field_id_idx ON stu_colony_fieldtype (field_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_colony_sandbox (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, colony_id INTEGER NOT NULL, name VARCHAR(255) NOT NULL, bev_work INTEGER NOT NULL, bev_max INTEGER NOT NULL, max_eps INTEGER NOT NULL, max_storage INTEGER NOT NULL, mask CLOB DEFAULT NULL, CONSTRAINT FK_7F824EAB96ADBADE FOREIGN KEY (colony_id) REFERENCES stu_colonies (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_7F824EAB96ADBADE ON stu_colony_sandbox (colony_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_colony_scan (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, colony_id INTEGER NOT NULL, user_id INTEGER NOT NULL, colony_user_id INTEGER NOT NULL, colony_name VARCHAR(255) DEFAULT NULL, colony_user_name VARCHAR(255) NOT NULL, mask CLOB NOT NULL, date INTEGER NOT NULL, CONSTRAINT FK_2853B5FC96ADBADE FOREIGN KEY (colony_id) REFERENCES stu_colonies (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_2853B5FCA76ED395 FOREIGN KEY (user_id) REFERENCES stu_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_2853B5FC96ADBADE ON stu_colony_scan (colony_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_2853B5FCA76ED395 ON stu_colony_scan (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_commodity (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, sort SMALLINT NOT NULL, "view" BOOLEAN NOT NULL, type SMALLINT NOT NULL, npc_commodity BOOLEAN NOT NULL, bound BOOLEAN NOT NULL)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_construction_progress (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, station_id INTEGER NOT NULL, remaining_ticks INTEGER NOT NULL, CONSTRAINT FK_57D2AD0421BDB235 FOREIGN KEY (station_id) REFERENCES stu_station (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_57D2AD0421BDB235 ON stu_construction_progress (station_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_contactlist (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, recipient INTEGER NOT NULL, mode SMALLINT NOT NULL, comment VARCHAR(50) NOT NULL, date INTEGER NOT NULL, CONSTRAINT FK_451BB4B0A76ED395 FOREIGN KEY (user_id) REFERENCES stu_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_451BB4B06804FB49 FOREIGN KEY (recipient) REFERENCES stu_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_451BB4B0A76ED395 ON stu_contactlist (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_451BB4B06804FB49 ON stu_contactlist (recipient)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX user_pair_idx ON stu_contactlist (user_id, recipient)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_crew (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, type SMALLINT NOT NULL, gender SMALLINT NOT NULL, name VARCHAR(255) NOT NULL, user_id INTEGER NOT NULL, race_id INTEGER NOT NULL, CONSTRAINT FK_167BE6E46E59D40D FOREIGN KEY (race_id) REFERENCES stu_crew_race (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_167BE6E4A76ED395 FOREIGN KEY (user_id) REFERENCES stu_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_167BE6E46E59D40D ON stu_crew (race_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_167BE6E4A76ED395 ON stu_crew (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_crew_assign (slot SMALLINT DEFAULT NULL, crew_id INTEGER NOT NULL, spacecraft_id INTEGER DEFAULT NULL, colony_id INTEGER DEFAULT NULL, tradepost_id INTEGER DEFAULT NULL, user_id INTEGER NOT NULL, repair_task_id INTEGER DEFAULT NULL, PRIMARY KEY(crew_id), CONSTRAINT FK_4793ED245FE259F6 FOREIGN KEY (crew_id) REFERENCES stu_crew (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_4793ED241C6AF6FD FOREIGN KEY (spacecraft_id) REFERENCES stu_spacecraft (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_4793ED2496ADBADE FOREIGN KEY (colony_id) REFERENCES stu_colonies (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_4793ED248B935ABD FOREIGN KEY (tradepost_id) REFERENCES stu_trade_posts (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_4793ED24A76ED395 FOREIGN KEY (user_id) REFERENCES stu_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_4793ED24130D5415 FOREIGN KEY (repair_task_id) REFERENCES stu_repair_task (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_4793ED241C6AF6FD ON stu_crew_assign (spacecraft_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_4793ED2496ADBADE ON stu_crew_assign (colony_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_4793ED248B935ABD ON stu_crew_assign (tradepost_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_4793ED24A76ED395 ON stu_crew_assign (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_4793ED24130D5415 ON stu_crew_assign (repair_task_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_crew_race (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, faction_id INTEGER NOT NULL, description VARCHAR(255) NOT NULL, chance SMALLINT NOT NULL, maleratio SMALLINT NOT NULL, define VARCHAR(255) NOT NULL, CONSTRAINT FK_ED3686294448F8DA FOREIGN KEY (faction_id) REFERENCES stu_factions (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_ED3686294448F8DA ON stu_crew_race (faction_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_crew_training (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, colony_id INTEGER NOT NULL, CONSTRAINT FK_E25756B996ADBADE FOREIGN KEY (colony_id) REFERENCES stu_colonies (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_E25756B9A76ED395 FOREIGN KEY (user_id) REFERENCES stu_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX crew_training_colony_idx ON stu_crew_training (colony_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX crew_training_user_idx ON stu_crew_training (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_database_categories (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, description VARCHAR(255) NOT NULL, points INTEGER NOT NULL, type INTEGER NOT NULL, sort INTEGER NOT NULL, prestige INTEGER NOT NULL)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_database_category_awards (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, category_id INTEGER NOT NULL, layer_id INTEGER DEFAULT NULL, award_id INTEGER DEFAULT NULL, CONSTRAINT FK_EEBC1A0512469DE2 FOREIGN KEY (category_id) REFERENCES stu_database_categories (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_EEBC1A053D5282CF FOREIGN KEY (award_id) REFERENCES stu_award (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_EEBC1A0512469DE2 ON stu_database_category_awards (category_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_EEBC1A053D5282CF ON stu_database_category_awards (award_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_database_entrys (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, description VARCHAR(255) NOT NULL, data CLOB NOT NULL, category_id INTEGER NOT NULL, type INTEGER NOT NULL, sort INTEGER NOT NULL, object_id INTEGER NOT NULL, layer_id INTEGER DEFAULT NULL, CONSTRAINT FK_4D14EE9A8CDE5729 FOREIGN KEY (type) REFERENCES stu_database_types (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_4D14EE9A12469DE2 FOREIGN KEY (category_id) REFERENCES stu_database_categories (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_4D14EE9A8CDE5729 ON stu_database_entrys (type)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX database_entry_category_id_idx ON stu_database_entrys (category_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_database_types (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, description VARCHAR(255) NOT NULL, macro VARCHAR(255) NOT NULL)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_database_user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, database_id INTEGER NOT NULL, user_id INTEGER NOT NULL, date INTEGER NOT NULL, CONSTRAINT FK_4148521FF0AA09DB FOREIGN KEY (database_id) REFERENCES stu_database_entrys (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_4148521FA76ED395 FOREIGN KEY (user_id) REFERENCES stu_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_4148521FF0AA09DB ON stu_database_user (database_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_4148521FA76ED395 ON stu_database_user (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_deals (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, faction_id INTEGER DEFAULT NULL, auction BOOLEAN NOT NULL, amount INTEGER DEFAULT NULL, give_commodity INTEGER DEFAULT NULL, want_commodity INTEGER DEFAULT NULL, give_commodity_amonut INTEGER DEFAULT NULL, want_commodity_amount INTEGER DEFAULT NULL, want_prestige INTEGER DEFAULT NULL, buildplan_id INTEGER DEFAULT NULL, ship BOOLEAN DEFAULT NULL, start INTEGER NOT NULL, "end" INTEGER NOT NULL, taken_time INTEGER DEFAULT NULL, auction_user INTEGER DEFAULT NULL, auction_amount INTEGER DEFAULT NULL, CONSTRAINT FK_6DAE42FCF27F1BE1 FOREIGN KEY (want_commodity) REFERENCES stu_commodity (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_6DAE42FCABA274E9 FOREIGN KEY (give_commodity) REFERENCES stu_commodity (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_6DAE42FC8638E4E7 FOREIGN KEY (buildplan_id) REFERENCES stu_buildplan (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6DAE42FCF27F1BE1 ON stu_deals (want_commodity)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6DAE42FCABA274E9 ON stu_deals (give_commodity)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6DAE42FC8638E4E7 ON stu_deals (buildplan_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_dockingrights (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, station_id INTEGER NOT NULL, target INTEGER NOT NULL, privilege_type SMALLINT NOT NULL, privilege_mode SMALLINT NOT NULL, CONSTRAINT FK_E7D4B2A21BDB235 FOREIGN KEY (station_id) REFERENCES stu_station (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX dockingrights_station_idx ON stu_dockingrights (station_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_factions (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description CLOB NOT NULL, darker_color VARCHAR(255) NOT NULL, chooseable BOOLEAN NOT NULL, player_limit INTEGER NOT NULL, start_building_id INTEGER NOT NULL, start_research_id INTEGER DEFAULT NULL, start_map_id INTEGER DEFAULT NULL, close_combat_score INTEGER DEFAULT NULL, positive_effect_primary_commodity_id INTEGER DEFAULT NULL, positive_effect_secondary_commodity_id INTEGER DEFAULT NULL, welcome_message CLOB DEFAULT NULL, CONSTRAINT FK_55D1F3CC237EF159 FOREIGN KEY (start_research_id) REFERENCES stu_research (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_55D1F3CC990AC8F4 FOREIGN KEY (start_map_id) REFERENCES stu_map (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_55D1F3CCFDE04DC5 FOREIGN KEY (positive_effect_primary_commodity_id) REFERENCES stu_commodity (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_55D1F3CC5840AFBB FOREIGN KEY (positive_effect_secondary_commodity_id) REFERENCES stu_commodity (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_55D1F3CC237EF159 ON stu_factions (start_research_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_55D1F3CC990AC8F4 ON stu_factions (start_map_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_55D1F3CCFDE04DC5 ON stu_factions (positive_effect_primary_commodity_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_55D1F3CC5840AFBB ON stu_factions (positive_effect_secondary_commodity_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_field_build (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, type INTEGER NOT NULL, buildings_id INTEGER NOT NULL, research_id INTEGER DEFAULT NULL, "view" BOOLEAN NOT NULL, CONSTRAINT FK_2785504A1485E613 FOREIGN KEY (buildings_id) REFERENCES stu_buildings (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_2785504A1485E613 ON stu_field_build (buildings_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX type_building_idx ON stu_field_build (type, buildings_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX type_building_research_idx ON stu_field_build (type, research_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_fleets (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(200) NOT NULL, user_id INTEGER NOT NULL, ships_id INTEGER NOT NULL, defended_colony_id INTEGER DEFAULT NULL, blocked_colony_id INTEGER DEFAULT NULL, sort INTEGER DEFAULT NULL, is_fixed BOOLEAN NOT NULL, CONSTRAINT FK_2042261BA76ED395 FOREIGN KEY (user_id) REFERENCES stu_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_2042261BC907E695 FOREIGN KEY (ships_id) REFERENCES stu_ship (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_2042261BF61193E4 FOREIGN KEY (defended_colony_id) REFERENCES stu_colonies (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_2042261B9722AC5D FOREIGN KEY (blocked_colony_id) REFERENCES stu_colonies (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_2042261BC907E695 ON stu_fleets (ships_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_2042261BF61193E4 ON stu_fleets (defended_colony_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_2042261B9722AC5D ON stu_fleets (blocked_colony_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX fleet_user_idx ON stu_fleets (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_flight_sig (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, ship_id INTEGER NOT NULL, rump_id INTEGER NOT NULL, time INTEGER NOT NULL, location_id INTEGER NOT NULL, from_direction SMALLINT DEFAULT NULL, to_direction SMALLINT DEFAULT NULL, ship_name VARCHAR(255) NOT NULL, is_cloaked BOOLEAN NOT NULL, CONSTRAINT FK_C789CFE12EE98D4C FOREIGN KEY (rump_id) REFERENCES stu_rump (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_C789CFE164D218E FOREIGN KEY (location_id) REFERENCES stu_location (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_C789CFE12EE98D4C ON stu_flight_sig (rump_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_C789CFE164D218E ON stu_flight_sig (location_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX flight_sig_user_idx ON stu_flight_sig (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX flight_sig_sensor_result_idx ON stu_flight_sig (from_direction, to_direction, time)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_game_config (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, option SMALLINT NOT NULL, value SMALLINT NOT NULL)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX option_idx ON stu_game_config (option)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_game_request (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER DEFAULT NULL, turn_id INTEGER NOT NULL, time INTEGER NOT NULL, module VARCHAR(255) DEFAULT NULL, "action" VARCHAR(255) DEFAULT NULL, action_ms INTEGER DEFAULT NULL, "view" VARCHAR(255) DEFAULT NULL, view_ms INTEGER DEFAULT NULL, render_ms INTEGER DEFAULT NULL, params CLOB DEFAULT NULL)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX game_request_idx ON stu_game_request (user_id, "action", "view")
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_game_turn_stats (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, turn_id INTEGER NOT NULL, user_count INTEGER NOT NULL, logins_24h INTEGER NOT NULL, inactive_count INTEGER NOT NULL, vacation_count INTEGER NOT NULL, ship_count INTEGER NOT NULL, ship_count_manned INTEGER NOT NULL, ship_count_npc INTEGER NOT NULL, kn_count INTEGER NOT NULL, flight_sig_24h INTEGER NOT NULL, flight_sig_system_24h INTEGER NOT NULL, new_pm_count INTEGER NOT NULL, CONSTRAINT FK_D3ABA4DB1F4F9889 FOREIGN KEY (turn_id) REFERENCES stu_game_turns (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_D3ABA4DB1F4F9889 ON stu_game_turn_stats (turn_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX game_turn_stats_turn_idx ON stu_game_turn_stats (turn_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_game_turns (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, turn INTEGER NOT NULL, startdate INTEGER NOT NULL, enddate INTEGER NOT NULL, pirate_fleets INTEGER DEFAULT NULL)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX turn_idx ON stu_game_turns (turn)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_history (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, text CLOB NOT NULL, date INTEGER NOT NULL, type SMALLINT NOT NULL, source_user_id INTEGER DEFAULT NULL, target_user_id INTEGER DEFAULT NULL, location_id INTEGER DEFAULT NULL, CONSTRAINT FK_7F01683964D218E FOREIGN KEY (location_id) REFERENCES stu_location (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_7F01683964D218E ON stu_history (location_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX type_idx ON stu_history (type)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_ignorelist (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, recipient INTEGER NOT NULL, date INTEGER NOT NULL, CONSTRAINT FK_B58C18ECA76ED395 FOREIGN KEY (user_id) REFERENCES stu_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_B58C18EC6804FB49 FOREIGN KEY (recipient) REFERENCES stu_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_B58C18ECA76ED395 ON stu_ignorelist (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_B58C18EC6804FB49 ON stu_ignorelist (recipient)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX user_recipient_idx ON stu_ignorelist (user_id, recipient)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_kn (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, titel VARCHAR(255) DEFAULT NULL, text CLOB NOT NULL, date INTEGER NOT NULL, username VARCHAR(255) NOT NULL, user_id INTEGER DEFAULT NULL, del_user_id INTEGER DEFAULT NULL, lastedit INTEGER NOT NULL, plot_id INTEGER DEFAULT NULL, deleted INTEGER DEFAULT NULL, ratings CLOB NOT NULL, CONSTRAINT FK_27245FD3680D0B01 FOREIGN KEY (plot_id) REFERENCES stu_plots (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_27245FD3A76ED395 FOREIGN KEY (user_id) REFERENCES stu_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX plot_idx ON stu_kn (plot_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX kn_post_date_idx ON stu_kn (date)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX kn_post_user_idx ON stu_kn (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_kn_archiv (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, version VARCHAR(255) NOT NULL, former_id INTEGER NOT NULL, titel VARCHAR(255) DEFAULT NULL, text CLOB NOT NULL, date INTEGER NOT NULL, username VARCHAR(255) NOT NULL, user_id INTEGER NOT NULL, del_user_id INTEGER DEFAULT NULL, lastedit INTEGER DEFAULT NULL, plot_id INTEGER DEFAULT NULL, ratings CLOB NOT NULL, CONSTRAINT FK_412525B680D0B01 FOREIGN KEY (plot_id) REFERENCES stu_plots_archiv (former_id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX plot_archiv_idx ON stu_kn_archiv (plot_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX kn_post_archiv_date_idx ON stu_kn_archiv (date)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX unique_kn_archiv_former_id ON stu_kn_archiv (former_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_kn_character (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, kn_id INTEGER NOT NULL, character_id INTEGER NOT NULL, CONSTRAINT FK_B00DC07A6A392D53 FOREIGN KEY (kn_id) REFERENCES stu_kn (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_B00DC07A1136BE75 FOREIGN KEY (character_id) REFERENCES stu_user_character (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_B00DC07A6A392D53 ON stu_kn_character (kn_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_B00DC07A1136BE75 ON stu_kn_character (character_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_kn_comments (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, post_id INTEGER NOT NULL, user_id INTEGER NOT NULL, username VARCHAR(255) NOT NULL, text VARCHAR(255) NOT NULL, date INTEGER NOT NULL, deleted INTEGER DEFAULT NULL, CONSTRAINT FK_F18967DD4B89032C FOREIGN KEY (post_id) REFERENCES stu_kn (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_F18967DDA76ED395 FOREIGN KEY (user_id) REFERENCES stu_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX kn_comment_post_idx ON stu_kn_comments (post_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX kn_comment_user_idx ON stu_kn_comments (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_kn_comments_archiv (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, version VARCHAR(255) NOT NULL, former_id INTEGER NOT NULL, post_id INTEGER NOT NULL, user_id INTEGER NOT NULL, username VARCHAR(255) NOT NULL, text VARCHAR(255) NOT NULL, date INTEGER NOT NULL, deleted INTEGER DEFAULT NULL, CONSTRAINT FK_378C11A44B89032C FOREIGN KEY (post_id) REFERENCES stu_kn_archiv (former_id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_378C11A44B89032C ON stu_kn_comments_archiv (post_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX unique_comments_former_id ON stu_kn_comments_archiv (former_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_kn_plot_application (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, post_id INTEGER NOT NULL, plot_id INTEGER NOT NULL, time INTEGER NOT NULL, CONSTRAINT FK_F342AF3C4B89032C FOREIGN KEY (post_id) REFERENCES stu_kn (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_F342AF3C680D0B01 FOREIGN KEY (plot_id) REFERENCES stu_plots (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_F342AF3C4B89032C ON stu_kn_plot_application (post_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_F342AF3C680D0B01 ON stu_kn_plot_application (plot_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_layer (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, width INTEGER NOT NULL, height INTEGER NOT NULL, is_hidden BOOLEAN NOT NULL, is_finished BOOLEAN DEFAULT NULL, is_encoded BOOLEAN DEFAULT NULL, award_id INTEGER DEFAULT NULL, description CLOB DEFAULT NULL, is_colonizable BOOLEAN DEFAULT NULL, is_noobzone BOOLEAN DEFAULT NULL, CONSTRAINT FK_664CE77D3D5282CF FOREIGN KEY (award_id) REFERENCES stu_award (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_664CE77D3D5282CF ON stu_layer (award_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_location (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, layer_id INTEGER DEFAULT NULL, cx INTEGER DEFAULT NULL, cy INTEGER DEFAULT NULL, field_id INTEGER NOT NULL, discr VARCHAR(255) NOT NULL, CONSTRAINT FK_E0CD22C3EA6EFDCD FOREIGN KEY (layer_id) REFERENCES stu_layer (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_E0CD22C3443707B0 FOREIGN KEY (field_id) REFERENCES stu_map_ftypes (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E0CD22C3EA6EFDCD ON stu_location (layer_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX location_coords_idx ON stu_location (layer_id, cx, cy)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX location_coords_reverse_idx ON stu_location (layer_id, cy, cx)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX location_field_type_idx ON stu_location (field_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_location_mining (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, location_id INTEGER NOT NULL, commodity_id INTEGER NOT NULL, actual_amount INTEGER NOT NULL, max_amount INTEGER NOT NULL, depleted_at INTEGER DEFAULT NULL, CONSTRAINT FK_AC85C1AC64D218E FOREIGN KEY (location_id) REFERENCES stu_location (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_AC85C1ACB4ACC212 FOREIGN KEY (commodity_id) REFERENCES stu_commodity (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_AC85C1AC64D218E ON stu_location_mining (location_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_AC85C1ACB4ACC212 ON stu_location_mining (commodity_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_lottery_buildplan (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, buildplan_id INTEGER NOT NULL, chance INTEGER NOT NULL, faction_id INTEGER DEFAULT NULL, CONSTRAINT FK_E8141D9B8638E4E7 FOREIGN KEY (buildplan_id) REFERENCES stu_buildplan (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E8141D9B8638E4E7 ON stu_lottery_buildplan (buildplan_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_lottery_ticket (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, period VARCHAR(255) NOT NULL, is_winner BOOLEAN DEFAULT NULL, CONSTRAINT FK_6300976CA76ED395 FOREIGN KEY (user_id) REFERENCES stu_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6300976CA76ED395 ON stu_lottery_ticket (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX lottery_ticket_period_idx ON stu_lottery_ticket (period)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_map (system_type_id INTEGER DEFAULT NULL, systems_id INTEGER DEFAULT NULL, influence_area_id INTEGER DEFAULT NULL, bordertype_id INTEGER DEFAULT NULL, region_id INTEGER DEFAULT NULL, admin_region_id INTEGER DEFAULT NULL, id INTEGER NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_1EEF59FD411D7F6D FOREIGN KEY (systems_id) REFERENCES stu_systems (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_1EEF59FD915ABAF6 FOREIGN KEY (influence_area_id) REFERENCES stu_systems (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_1EEF59FD293FF000 FOREIGN KEY (system_type_id) REFERENCES stu_system_types (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_1EEF59FDE5FE943D FOREIGN KEY (bordertype_id) REFERENCES stu_map_bordertypes (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_1EEF59FD98260155 FOREIGN KEY (region_id) REFERENCES stu_map_regions (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_1EEF59FD71D10467 FOREIGN KEY (admin_region_id) REFERENCES stu_map_regions (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_1EEF59FDBF396750 FOREIGN KEY (id) REFERENCES stu_location (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_1EEF59FD411D7F6D ON stu_map (systems_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_1EEF59FD98260155 ON stu_map (region_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX map_system_idx ON stu_map (systems_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX map_system_type_idx ON stu_map (system_type_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX map_influence_area_idx ON stu_map (influence_area_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX map_bordertype_idx ON stu_map (bordertype_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX map_admin_region_idx ON stu_map (admin_region_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_map_bordertypes (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, faction_id INTEGER NOT NULL, color VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_map_ftypes (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, type INTEGER NOT NULL, is_system BOOLEAN NOT NULL, ecost SMALLINT NOT NULL, name VARCHAR(255) NOT NULL, colonies_classes_id INTEGER DEFAULT NULL, damage SMALLINT NOT NULL, x_damage SMALLINT NOT NULL, x_damage_system SMALLINT DEFAULT NULL, x_damage_type SMALLINT DEFAULT NULL, "view" BOOLEAN NOT NULL, passable BOOLEAN NOT NULL, complementary_color VARCHAR(255) DEFAULT NULL, effects CLOB DEFAULT NULL, CONSTRAINT FK_3D24A6CE9106126 FOREIGN KEY (colonies_classes_id) REFERENCES stu_colonies_classes (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3D24A6CE9106126 ON stu_map_ftypes (colonies_classes_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX map_ftypes_type_idx ON stu_map_ftypes (type)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_map_regions (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, description VARCHAR(255) NOT NULL, database_id INTEGER DEFAULT NULL, layers VARCHAR(255) DEFAULT NULL, CONSTRAINT FK_3CCEB5C5F0AA09DB FOREIGN KEY (database_id) REFERENCES stu_database_entrys (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3CCEB5C5F0AA09DB ON stu_map_regions (database_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_map_regions_settlement (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, region_id INTEGER NOT NULL, faction_id INTEGER NOT NULL)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_mass_center_type (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, description VARCHAR(255) NOT NULL, size INTEGER NOT NULL, first_field_type_id INTEGER NOT NULL, CONSTRAINT FK_81F0ECD2AC15FEB7 FOREIGN KEY (first_field_type_id) REFERENCES stu_map_ftypes (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_81F0ECD2AC15FEB7 ON stu_mass_center_type (first_field_type_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX mass_center_field_type_idx ON stu_mass_center_type (first_field_type_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_mining_queue (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, ship_id INTEGER NOT NULL, location_mining_id INTEGER NOT NULL, CONSTRAINT FK_BBFEF8C427D56C25 FOREIGN KEY (location_mining_id) REFERENCES stu_location_mining (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_BBFEF8C4C256317D FOREIGN KEY (ship_id) REFERENCES stu_ship (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_BBFEF8C427D56C25 ON stu_mining_queue (location_mining_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_BBFEF8C4C256317D ON stu_mining_queue (ship_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_modules (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, level SMALLINT NOT NULL, upgrade_factor SMALLINT NOT NULL, default_factor SMALLINT NOT NULL, downgrade_factor SMALLINT NOT NULL, crew SMALLINT NOT NULL, type INTEGER NOT NULL, research_id INTEGER DEFAULT NULL, commodity_id INTEGER NOT NULL, viewable BOOLEAN NOT NULL, rumps_role_id INTEGER DEFAULT NULL, ecost SMALLINT NOT NULL, faction_id INTEGER DEFAULT NULL, system_type INTEGER DEFAULT NULL, CONSTRAINT FK_760C5BA57909E1ED FOREIGN KEY (research_id) REFERENCES stu_research (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_760C5BA5B4ACC212 FOREIGN KEY (commodity_id) REFERENCES stu_commodity (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_760C5BA54448F8DA FOREIGN KEY (faction_id) REFERENCES stu_factions (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_760C5BA5A7BC4A6 FOREIGN KEY (rumps_role_id) REFERENCES stu_rumps_roles (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_760C5BA57909E1ED ON stu_modules (research_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_760C5BA5B4ACC212 ON stu_modules (commodity_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_760C5BA54448F8DA ON stu_modules (faction_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_760C5BA5A7BC4A6 ON stu_modules (rumps_role_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX ship_rump_role_type_idx ON stu_modules (rumps_role_id, type)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_modules_buildingfunction (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, module_id INTEGER NOT NULL, buildingfunction INTEGER NOT NULL, CONSTRAINT FK_85B4A88EAFC2B591 FOREIGN KEY (module_id) REFERENCES stu_modules (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_85B4A88EAFC2B591 ON stu_modules_buildingfunction (module_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX module_buildingfunction_idx ON stu_modules_buildingfunction (module_id, buildingfunction)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_modules_cost (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, module_id INTEGER NOT NULL, commodity_id INTEGER NOT NULL, count INTEGER NOT NULL, CONSTRAINT FK_91B75BB6B4ACC212 FOREIGN KEY (commodity_id) REFERENCES stu_commodity (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_91B75BB6AFC2B591 FOREIGN KEY (module_id) REFERENCES stu_modules (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_91B75BB6B4ACC212 ON stu_modules_cost (commodity_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX module_cost_module_idx ON stu_modules_cost (module_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_modules_queue (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, colony_id INTEGER NOT NULL, module_id INTEGER NOT NULL, count INTEGER NOT NULL, buildingfunction INTEGER NOT NULL, CONSTRAINT FK_E97D4622AFC2B591 FOREIGN KEY (module_id) REFERENCES stu_modules (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_E97D462296ADBADE FOREIGN KEY (colony_id) REFERENCES stu_colonies (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E97D4622AFC2B591 ON stu_modules_queue (module_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E97D462296ADBADE ON stu_modules_queue (colony_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX module_queue_colony_module_idx ON stu_modules_queue (colony_id, module_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_modules_specials (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, module_id INTEGER NOT NULL, special_id SMALLINT NOT NULL, CONSTRAINT FK_B025FE0DAFC2B591 FOREIGN KEY (module_id) REFERENCES stu_modules (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX module_special_module_idx ON stu_modules_specials (module_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_names (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, count INTEGER DEFAULT NULL, type INTEGER NOT NULL)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_news (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, subject VARCHAR(255) NOT NULL, text CLOB NOT NULL, date INTEGER NOT NULL, refs CLOB NOT NULL)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX news_date_idx ON stu_news (date)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_notes (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, date INTEGER NOT NULL, title VARCHAR(255) NOT NULL, text CLOB NOT NULL, CONSTRAINT FK_838C60EBA76ED395 FOREIGN KEY (user_id) REFERENCES stu_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX note_user_idx ON stu_notes (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_npc_log (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, text CLOB NOT NULL, date INTEGER NOT NULL, source_user_id INTEGER DEFAULT NULL, faction_id INTEGER DEFAULT NULL)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_opened_advent_door (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, day INTEGER NOT NULL, year INTEGER NOT NULL, time INTEGER NOT NULL)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_partnersite (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, text CLOB NOT NULL, banner VARCHAR(200) NOT NULL)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_pirate_round (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, start INTEGER NOT NULL, end_time INTEGER DEFAULT NULL, max_prestige INTEGER NOT NULL, actual_prestige INTEGER NOT NULL, faction_winner INTEGER DEFAULT NULL)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_pirate_setup (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(200) NOT NULL, probability_weight INTEGER NOT NULL)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_pirate_setup_buildplan (amount INTEGER NOT NULL, pirate_setup_id INTEGER NOT NULL, buildplan_id INTEGER NOT NULL, PRIMARY KEY(pirate_setup_id, buildplan_id), CONSTRAINT FK_A99F4154E66A498F FOREIGN KEY (pirate_setup_id) REFERENCES stu_pirate_setup (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_A99F41548638E4E7 FOREIGN KEY (buildplan_id) REFERENCES stu_buildplan (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_A99F4154E66A498F ON stu_pirate_setup_buildplan (pirate_setup_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_A99F41548638E4E7 ON stu_pirate_setup_buildplan (buildplan_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_pirate_wrath (wrath INTEGER NOT NULL, protection_timeout INTEGER DEFAULT NULL, user_id INTEGER NOT NULL, PRIMARY KEY(user_id), CONSTRAINT FK_A7D3C35BA76ED395 FOREIGN KEY (user_id) REFERENCES stu_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_planet_type_research (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, research_id INTEGER NOT NULL, planet_type_id INTEGER NOT NULL, CONSTRAINT FK_5C1857F87909E1ED FOREIGN KEY (research_id) REFERENCES stu_research (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_5C1857F8506FDB14 FOREIGN KEY (planet_type_id) REFERENCES stu_colonies_classes (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_5C1857F87909E1ED ON stu_planet_type_research (research_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX planet_type_idx ON stu_planet_type_research (planet_type_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_planets_commodity (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, planet_classes_id INTEGER NOT NULL, commodity_id INTEGER NOT NULL, count SMALLINT NOT NULL)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX planet_classes_idx ON stu_planets_commodity (planet_classes_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_plots (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, title VARCHAR(255) NOT NULL, description CLOB NOT NULL, start_date INTEGER NOT NULL, end_date INTEGER DEFAULT NULL, CONSTRAINT FK_D438967A76ED395 FOREIGN KEY (user_id) REFERENCES stu_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX rpg_plot_end_date_idx ON stu_plots (end_date)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX rpg_plot_user_idx ON stu_plots (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_plots_archiv (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, version VARCHAR(255) NOT NULL, former_id INTEGER NOT NULL, user_id INTEGER NOT NULL, title VARCHAR(255) NOT NULL, description CLOB NOT NULL, start_date INTEGER NOT NULL, end_date INTEGER DEFAULT NULL)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX rpg_plot_archiv_end_date_idx ON stu_plots_archiv (end_date)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX unique_plot_id ON stu_plots_archiv (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX unique_former_id ON stu_plots_archiv (former_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_plots_members (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, plot_id INTEGER NOT NULL, user_id INTEGER NOT NULL, CONSTRAINT FK_87148972680D0B01 FOREIGN KEY (plot_id) REFERENCES stu_plots (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_87148972A76ED395 FOREIGN KEY (user_id) REFERENCES stu_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_87148972680D0B01 ON stu_plots_members (plot_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_87148972A76ED395 ON stu_plots_members (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX plot_user_idx ON stu_plots_members (plot_id, user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_plots_members_archiv (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, version VARCHAR(255) NOT NULL, former_id INTEGER NOT NULL, plot_id INTEGER NOT NULL, user_id INTEGER NOT NULL, CONSTRAINT FK_CDE3D430680D0B01 FOREIGN KEY (plot_id) REFERENCES stu_plots_archiv (former_id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_CDE3D430680D0B01 ON stu_plots_members_archiv (plot_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX plot_archiv_user_idx ON stu_plots_members_archiv (plot_id, user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_pm_cats (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, description VARCHAR(255) NOT NULL, sort SMALLINT NOT NULL, special SMALLINT NOT NULL, deleted INTEGER DEFAULT NULL, CONSTRAINT FK_C54F3637A76ED395 FOREIGN KEY (user_id) REFERENCES stu_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_C54F3637A76ED395 ON stu_pm_cats (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX user_special_idx ON stu_pm_cats (user_id, special)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_pms (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, send_user INTEGER NOT NULL, recip_user INTEGER NOT NULL, text CLOB NOT NULL, date INTEGER NOT NULL, new BOOLEAN NOT NULL, cat_id INTEGER NOT NULL, inbox_pm_id INTEGER DEFAULT NULL, href VARCHAR(255) DEFAULT NULL, deleted INTEGER DEFAULT NULL, CONSTRAINT FK_3FAD7768E6ADA943 FOREIGN KEY (cat_id) REFERENCES stu_pm_cats (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_3FAD7768E9546EDA FOREIGN KEY (send_user) REFERENCES stu_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_3FAD77685A3E0BD8 FOREIGN KEY (recip_user) REFERENCES stu_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_3FAD77687FAFC1FA FOREIGN KEY (inbox_pm_id) REFERENCES stu_pms (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3FAD7768E6ADA943 ON stu_pms (cat_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3FAD7768E9546EDA ON stu_pms (send_user)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3FAD77685A3E0BD8 ON stu_pms (recip_user)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_3FAD77687FAFC1FA ON stu_pms (inbox_pm_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX recipient_folder_idx ON stu_pms (recip_user, cat_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX correspondence ON stu_pms (recip_user, send_user)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX pm_date_idx ON stu_pms (date)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_prestige_log (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, amount INTEGER NOT NULL, description CLOB NOT NULL, date INTEGER NOT NULL)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX prestige_log_user_idx ON stu_prestige_log (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_progress_module (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, progress_id INTEGER NOT NULL, module_id INTEGER NOT NULL, CONSTRAINT FK_7FE7540743DB87C9 FOREIGN KEY (progress_id) REFERENCES stu_construction_progress (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_7FE75407AFC2B591 FOREIGN KEY (module_id) REFERENCES stu_modules (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_7FE7540743DB87C9 ON stu_progress_module (progress_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_7FE75407AFC2B591 ON stu_progress_module (module_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_repair_task (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, spacecraft_id INTEGER NOT NULL, finish_time INTEGER NOT NULL, system_type INTEGER NOT NULL, healing_percentage INTEGER NOT NULL, CONSTRAINT FK_36DA3BAFA76ED395 FOREIGN KEY (user_id) REFERENCES stu_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_36DA3BAF1C6AF6FD FOREIGN KEY (spacecraft_id) REFERENCES stu_spacecraft (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_36DA3BAFA76ED395 ON stu_repair_task (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_36DA3BAF1C6AF6FD ON stu_repair_task (spacecraft_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_research (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description CLOB NOT NULL, sort SMALLINT NOT NULL, rump_id INTEGER NOT NULL, database_entries CLOB NOT NULL, points SMALLINT NOT NULL, commodity_id INTEGER NOT NULL, reward_buildplan_id INTEGER DEFAULT NULL, award_id INTEGER DEFAULT NULL, needed_award INTEGER DEFAULT NULL, upper_limit_colony_type SMALLINT DEFAULT NULL, upper_limit_colony_amount SMALLINT DEFAULT NULL, CONSTRAINT FK_E9B8FBCAB4ACC212 FOREIGN KEY (commodity_id) REFERENCES stu_commodity (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_E9B8FBCAEEF0C2F2 FOREIGN KEY (reward_buildplan_id) REFERENCES stu_buildplan (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_E9B8FBCA3D5282CF FOREIGN KEY (award_id) REFERENCES stu_award (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E9B8FBCAB4ACC212 ON stu_research (commodity_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E9B8FBCAEEF0C2F2 ON stu_research (reward_buildplan_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E9B8FBCA3D5282CF ON stu_research (award_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_research_dependencies (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, research_id INTEGER NOT NULL, depends_on INTEGER NOT NULL, mode SMALLINT NOT NULL, CONSTRAINT FK_8C4C33D97909E1ED FOREIGN KEY (research_id) REFERENCES stu_research (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_8C4C33D950E5182A FOREIGN KEY (depends_on) REFERENCES stu_research (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_8C4C33D97909E1ED ON stu_research_dependencies (research_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_8C4C33D950E5182A ON stu_research_dependencies (depends_on)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_researched (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, research_id INTEGER NOT NULL, user_id INTEGER NOT NULL, aktiv INTEGER NOT NULL, finished INTEGER NOT NULL, CONSTRAINT FK_8F6D5B47909E1ED FOREIGN KEY (research_id) REFERENCES stu_research (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_8F6D5B4A76ED395 FOREIGN KEY (user_id) REFERENCES stu_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_8F6D5B47909E1ED ON stu_researched (research_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_8F6D5B4A76ED395 ON stu_researched (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_rump (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, category_id INTEGER NOT NULL, role_id INTEGER DEFAULT NULL, evade_chance SMALLINT NOT NULL, hit_chance SMALLINT NOT NULL, module_level SMALLINT NOT NULL, base_crew SMALLINT NOT NULL, base_eps SMALLINT NOT NULL, base_reactor SMALLINT NOT NULL, base_hull INTEGER NOT NULL, base_shield INTEGER NOT NULL, base_damage SMALLINT NOT NULL, base_sensor_range SMALLINT NOT NULL, base_torpedo_storage SMALLINT NOT NULL, phaser_volleys SMALLINT NOT NULL, phaser_hull_damage_factor SMALLINT NOT NULL, phaser_shield_damage_factor SMALLINT NOT NULL, torpedo_level SMALLINT NOT NULL, torpedo_volleys SMALLINT NOT NULL, name VARCHAR(255) NOT NULL, is_buildable BOOLEAN NOT NULL, is_npc BOOLEAN NOT NULL, eps_cost SMALLINT NOT NULL, storage INTEGER NOT NULL, slots SMALLINT NOT NULL, buildtime INTEGER NOT NULL, needed_workbees SMALLINT DEFAULT NULL, sort SMALLINT NOT NULL, database_id INTEGER DEFAULT NULL, commodity_id INTEGER DEFAULT NULL, flight_ecost SMALLINT NOT NULL, beam_factor SMALLINT NOT NULL, special_slots SMALLINT NOT NULL, shuttle_slots SMALLINT NOT NULL, tractor_mass INTEGER NOT NULL, tractor_payload INTEGER NOT NULL, prestige INTEGER NOT NULL, base_warpdrive INTEGER NOT NULL, npc_buildable BOOLEAN DEFAULT NULL, CONSTRAINT FK_AD2CDF30D60322AC FOREIGN KEY (role_id) REFERENCES stu_rumps_roles (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_AD2CDF3012469DE2 FOREIGN KEY (category_id) REFERENCES stu_rumps_categories (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_AD2CDF30B4ACC212 FOREIGN KEY (commodity_id) REFERENCES stu_commodity (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_AD2CDF30F0AA09DB FOREIGN KEY (database_id) REFERENCES stu_database_entrys (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_AD2CDF30B4ACC212 ON stu_rump (commodity_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_AD2CDF30F0AA09DB ON stu_rump (database_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX rump_category_idx ON stu_rump (category_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX rump_role_idx ON stu_rump (role_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_rump_costs (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, rump_id INTEGER NOT NULL, commodity_id INTEGER NOT NULL, count INTEGER NOT NULL, CONSTRAINT FK_11BE8AA4B4ACC212 FOREIGN KEY (commodity_id) REFERENCES stu_commodity (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_11BE8AA42EE98D4C FOREIGN KEY (rump_id) REFERENCES stu_rump (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_11BE8AA4B4ACC212 ON stu_rump_costs (commodity_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX rump_cost_ship_rump_idx ON stu_rump_costs (rump_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_rumps_buildingfunction (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, rump_id INTEGER NOT NULL, building_function INTEGER NOT NULL)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX building_function_ship_rump_idx ON stu_rumps_buildingfunction (rump_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX building_function_idx ON stu_rumps_buildingfunction (building_function)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_rumps_cat_role_crew (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, rump_category_id INTEGER NOT NULL, rump_role_id INTEGER NOT NULL, job_1_crew SMALLINT NOT NULL, job_2_crew SMALLINT NOT NULL, job_3_crew SMALLINT NOT NULL, job_4_crew SMALLINT NOT NULL, job_5_crew SMALLINT NOT NULL, job_6_crew SMALLINT NOT NULL, job_7_crew SMALLINT NOT NULL, CONSTRAINT FK_976EF6322F027718 FOREIGN KEY (rump_role_id) REFERENCES stu_rumps_roles (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_976EF6322F027718 ON stu_rumps_cat_role_crew (rump_role_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX ship_rump_category_role_idx ON stu_rumps_cat_role_crew (rump_category_id, rump_role_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_rumps_categories (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, database_id INTEGER DEFAULT NULL, type VARCHAR(255) NOT NULL, CONSTRAINT FK_D23A3E0BF0AA09DB FOREIGN KEY (database_id) REFERENCES stu_database_entrys (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_D23A3E0BF0AA09DB ON stu_rumps_categories (database_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_rumps_colonize_building (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, rump_id INTEGER NOT NULL, building_id INTEGER NOT NULL)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX rump_colonize_building_ship_rump_idx ON stu_rumps_colonize_building (rump_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_rumps_module_level (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, rump_id INTEGER NOT NULL, module_level_1 SMALLINT NOT NULL, module_mandatory_1 SMALLINT NOT NULL, module_level_1_min SMALLINT NOT NULL, module_level_1_max SMALLINT NOT NULL, module_level_2 SMALLINT NOT NULL, module_mandatory_2 SMALLINT NOT NULL, module_level_2_min SMALLINT NOT NULL, module_level_2_max SMALLINT NOT NULL, module_level_3 SMALLINT NOT NULL, module_mandatory_3 SMALLINT NOT NULL, module_level_3_min SMALLINT NOT NULL, module_level_3_max SMALLINT NOT NULL, module_level_4 SMALLINT NOT NULL, module_mandatory_4 SMALLINT NOT NULL, module_level_4_min SMALLINT NOT NULL, module_level_4_max SMALLINT NOT NULL, module_level_5 SMALLINT NOT NULL, module_mandatory_5 SMALLINT NOT NULL, module_level_5_min SMALLINT NOT NULL, module_level_5_max SMALLINT NOT NULL, module_level_6 SMALLINT NOT NULL, module_mandatory_6 SMALLINT NOT NULL, module_level_6_min SMALLINT NOT NULL, module_level_6_max SMALLINT NOT NULL, module_level_7 SMALLINT NOT NULL, module_mandatory_7 SMALLINT NOT NULL, module_level_7_min SMALLINT NOT NULL, module_level_7_max SMALLINT NOT NULL, module_level_8 SMALLINT NOT NULL, module_mandatory_8 SMALLINT NOT NULL, module_level_8_min SMALLINT NOT NULL, module_level_8_max SMALLINT NOT NULL, module_level_10 SMALLINT NOT NULL, module_mandatory_10 SMALLINT NOT NULL, module_level_10_min SMALLINT NOT NULL, module_level_10_max SMALLINT NOT NULL, module_level_11 SMALLINT NOT NULL, module_mandatory_11 SMALLINT NOT NULL, module_level_11_min SMALLINT NOT NULL, module_level_11_max SMALLINT NOT NULL)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX rump_module_level_ship_rump_idx ON stu_rumps_module_level (rump_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_rumps_module_special (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, rump_id INTEGER NOT NULL, module_special_id INTEGER NOT NULL)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX rump_module_special_ship_rump_idx ON stu_rumps_module_special (rump_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_rumps_roles (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_rumps_specials (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, rump_id INTEGER NOT NULL, special INTEGER NOT NULL, CONSTRAINT FK_3F94D9B22EE98D4C FOREIGN KEY (rump_id) REFERENCES stu_rump (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX rump_special_ship_rump_idx ON stu_rumps_specials (rump_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_rumps_user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, rump_id INTEGER NOT NULL, user_id INTEGER NOT NULL, CONSTRAINT FK_1E7BBE13A76ED395 FOREIGN KEY (user_id) REFERENCES stu_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_1E7BBE13A76ED395 ON stu_rumps_user (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX rump_user_idx ON stu_rumps_user (rump_id, user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_session_strings (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, sess_string VARCHAR(255) NOT NULL, date DATETIME DEFAULT NULL, CONSTRAINT FK_6468CB57A76ED395 FOREIGN KEY (user_id) REFERENCES stu_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6468CB57A76ED395 ON stu_session_strings (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX session_string_user_idx ON stu_session_strings (sess_string, user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX session_string_date_idx ON stu_session_strings (date)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_ship (fleet_id INTEGER DEFAULT NULL, docked_to_id INTEGER DEFAULT NULL, is_fleet_leader BOOLEAN NOT NULL, id INTEGER NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_65024D724B061DF9 FOREIGN KEY (fleet_id) REFERENCES stu_fleets (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_65024D729B76929F FOREIGN KEY (docked_to_id) REFERENCES stu_station (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_65024D72BF396750 FOREIGN KEY (id) REFERENCES stu_spacecraft (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_65024D724B061DF9 ON stu_ship (fleet_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_65024D729B76929F ON stu_ship (docked_to_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_ship_log (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, spacecraft_id INTEGER NOT NULL, text CLOB NOT NULL, date INTEGER NOT NULL, is_private BOOLEAN NOT NULL, deleted INTEGER DEFAULT NULL, CONSTRAINT FK_74CEF0EE1C6AF6FD FOREIGN KEY (spacecraft_id) REFERENCES stu_spacecraft (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_74CEF0EE1C6AF6FD ON stu_ship_log (spacecraft_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_ship_takeover (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, source_spacecraft_id INTEGER NOT NULL, target_spacecraft_id INTEGER NOT NULL, start_turn INTEGER NOT NULL, prestige INTEGER NOT NULL, CONSTRAINT FK_4B0B8A7CD906279F FOREIGN KEY (source_spacecraft_id) REFERENCES stu_spacecraft (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_4B0B8A7CE6C54C FOREIGN KEY (target_spacecraft_id) REFERENCES stu_spacecraft (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_4B0B8A7CD906279F ON stu_ship_takeover (source_spacecraft_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_4B0B8A7CE6C54C ON stu_ship_takeover (target_spacecraft_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX ship_takeover_source_idx ON stu_ship_takeover (source_spacecraft_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX ship_takeover_target_idx ON stu_ship_takeover (target_spacecraft_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_shipyard_shipqueue (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, station_id INTEGER NOT NULL, user_id INTEGER NOT NULL, rump_id INTEGER NOT NULL, buildplan_id INTEGER NOT NULL, buildtime INTEGER NOT NULL, finish_date INTEGER NOT NULL, stop_date INTEGER NOT NULL, CONSTRAINT FK_7C6FFB428638E4E7 FOREIGN KEY (buildplan_id) REFERENCES stu_buildplan (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_7C6FFB422EE98D4C FOREIGN KEY (rump_id) REFERENCES stu_rump (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_7C6FFB4221BDB235 FOREIGN KEY (station_id) REFERENCES stu_station (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_7C6FFB428638E4E7 ON stu_shipyard_shipqueue (buildplan_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_7C6FFB422EE98D4C ON stu_shipyard_shipqueue (rump_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_7C6FFB4221BDB235 ON stu_shipyard_shipqueue (station_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX shipyard_shipqueue_user_idx ON stu_shipyard_shipqueue (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX shipyard_shipqueue_finish_date_idx ON stu_shipyard_shipqueue (finish_date)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_spacecraft (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, rump_id INTEGER NOT NULL, plan_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, max_huelle INTEGER NOT NULL, max_schilde INTEGER NOT NULL, tractored_ship_id INTEGER DEFAULT NULL, holding_web_id INTEGER DEFAULT NULL, database_id INTEGER DEFAULT NULL, location_id INTEGER NOT NULL, type VARCHAR(255) NOT NULL, CONSTRAINT FK_4BD20E2EEE54A42E FOREIGN KEY (tractored_ship_id) REFERENCES stu_ship (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_4BD20E2E73D3801E FOREIGN KEY (holding_web_id) REFERENCES stu_tholian_web (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_4BD20E2EA76ED395 FOREIGN KEY (user_id) REFERENCES stu_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_4BD20E2E2EE98D4C FOREIGN KEY (rump_id) REFERENCES stu_rump (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_4BD20E2EE899029B FOREIGN KEY (plan_id) REFERENCES stu_buildplan (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_4BD20E2E64D218E FOREIGN KEY (location_id) REFERENCES stu_location (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_4BD20E2EEE54A42E ON stu_spacecraft (tractored_ship_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_4BD20E2E73D3801E ON stu_spacecraft (holding_web_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_4BD20E2EA76ED395 ON stu_spacecraft (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_4BD20E2E2EE98D4C ON stu_spacecraft (rump_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_4BD20E2EE899029B ON stu_spacecraft (plan_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_4BD20E2E64D218E ON stu_spacecraft (location_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_spacecraft_condition (hull INTEGER NOT NULL, shield INTEGER NOT NULL, is_disabled BOOLEAN NOT NULL, state SMALLINT NOT NULL, spacecraft_id INTEGER NOT NULL, PRIMARY KEY(spacecraft_id), CONSTRAINT FK_6B28914D1C6AF6FD FOREIGN KEY (spacecraft_id) REFERENCES stu_spacecraft (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_spacecraft_emergency (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, spacecraft_id INTEGER NOT NULL, text CLOB NOT NULL, date INTEGER NOT NULL, deleted INTEGER DEFAULT NULL, CONSTRAINT FK_F02308131C6AF6FD FOREIGN KEY (spacecraft_id) REFERENCES stu_spacecraft (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_F02308131C6AF6FD ON stu_spacecraft_emergency (spacecraft_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_spacecraft_system (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, spacecraft_id INTEGER NOT NULL, system_type SMALLINT NOT NULL, module_id INTEGER DEFAULT NULL, status SMALLINT NOT NULL, mode SMALLINT NOT NULL, cooldown INTEGER DEFAULT NULL, data CLOB DEFAULT NULL, CONSTRAINT FK_2AD626BCAFC2B591 FOREIGN KEY (module_id) REFERENCES stu_modules (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_2AD626BC1C6AF6FD FOREIGN KEY (spacecraft_id) REFERENCES stu_spacecraft (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_2AD626BCAFC2B591 ON stu_spacecraft_system (module_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_2AD626BC1C6AF6FD ON stu_spacecraft_system (spacecraft_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX spacecraft_system_status_idx ON stu_spacecraft_system (status)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX spacecraft_system_type_idx ON stu_spacecraft_system (system_type)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX spacecraft_system_mode_idx ON stu_spacecraft_system (mode)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_station (influence_area_id INTEGER DEFAULT NULL, id INTEGER NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_C782E0C3915ABAF6 FOREIGN KEY (influence_area_id) REFERENCES stu_systems (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_C782E0C3BF396750 FOREIGN KEY (id) REFERENCES stu_spacecraft (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_C782E0C3915ABAF6 ON stu_station (influence_area_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX station_influence_area_idx ON stu_station (influence_area_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_station_shiprepair (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, station_id INTEGER NOT NULL, ship_id INTEGER NOT NULL, CONSTRAINT FK_51875AF721BDB235 FOREIGN KEY (station_id) REFERENCES stu_station (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_51875AF7C256317D FOREIGN KEY (ship_id) REFERENCES stu_ship (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_51875AF721BDB235 ON stu_station_shiprepair (station_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_51875AF7C256317D ON stu_station_shiprepair (ship_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_storage (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, commodity_id INTEGER NOT NULL, count INTEGER NOT NULL, colony_id INTEGER DEFAULT NULL, spacecraft_id INTEGER DEFAULT NULL, torpedo_storage_id INTEGER DEFAULT NULL, tradepost_id INTEGER DEFAULT NULL, tradeoffer_id INTEGER DEFAULT NULL, trumfield_id INTEGER DEFAULT NULL, CONSTRAINT FK_CC10346A76ED395 FOREIGN KEY (user_id) REFERENCES stu_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_CC10346B4ACC212 FOREIGN KEY (commodity_id) REFERENCES stu_commodity (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_CC1034696ADBADE FOREIGN KEY (colony_id) REFERENCES stu_colonies (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_CC103461C6AF6FD FOREIGN KEY (spacecraft_id) REFERENCES stu_spacecraft (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_CC1034686DD9CFF FOREIGN KEY (torpedo_storage_id) REFERENCES stu_torpedo_storage (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_CC103468B935ABD FOREIGN KEY (tradepost_id) REFERENCES stu_trade_posts (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_CC10346D40ECD65 FOREIGN KEY (tradeoffer_id) REFERENCES stu_trade_offers (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_CC10346668E6720 FOREIGN KEY (trumfield_id) REFERENCES stu_trumfield (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_CC1034686DD9CFF ON stu_storage (torpedo_storage_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_CC10346D40ECD65 ON stu_storage (tradeoffer_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_CC10346668E6720 ON stu_storage (trumfield_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX storage_user_idx ON stu_storage (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX storage_commodity_idx ON stu_storage (commodity_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX storage_colony_idx ON stu_storage (colony_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX storage_spacecraft_idx ON stu_storage (spacecraft_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX storage_torpedo_idx ON stu_storage (torpedo_storage_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX storage_tradepost_idx ON stu_storage (tradepost_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX storage_tradeoffer_idx ON stu_storage (tradeoffer_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_sys_map (sx SMALLINT NOT NULL, sy SMALLINT NOT NULL, systems_id INTEGER NOT NULL, id INTEGER NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_758AA7F1411D7F6D FOREIGN KEY (systems_id) REFERENCES stu_systems (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_758AA7F1BF396750 FOREIGN KEY (id) REFERENCES stu_location (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_758AA7F1411D7F6D ON stu_sys_map (systems_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX system_coordinates_idx ON stu_sys_map (sx, sy, systems_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_system_types (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, description VARCHAR(255) NOT NULL, database_id INTEGER DEFAULT NULL, is_generateable BOOLEAN DEFAULT NULL, first_mass_center_id INTEGER DEFAULT NULL, second_mass_center_id INTEGER DEFAULT NULL, CONSTRAINT FK_A02918D8F0AA09DB FOREIGN KEY (database_id) REFERENCES stu_database_entrys (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_A02918D8109BE2EA FOREIGN KEY (first_mass_center_id) REFERENCES stu_mass_center_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_A02918D8E1E58576 FOREIGN KEY (second_mass_center_id) REFERENCES stu_mass_center_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_A02918D8F0AA09DB ON stu_system_types (database_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX starsystem_mass_center_1_idx ON stu_system_types (first_mass_center_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX starsystem_mass_center_2_idx ON stu_system_types (second_mass_center_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_systems (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, type INTEGER NOT NULL, name VARCHAR(255) NOT NULL, max_x SMALLINT NOT NULL, max_y SMALLINT NOT NULL, bonus_fields SMALLINT NOT NULL, database_id INTEGER DEFAULT NULL, is_wormhole BOOLEAN NOT NULL, CONSTRAINT FK_3916C0C08CDE5729 FOREIGN KEY (type) REFERENCES stu_system_types (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_3916C0C0F0AA09DB FOREIGN KEY (database_id) REFERENCES stu_database_entrys (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3916C0C08CDE5729 ON stu_systems (type)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3916C0C0F0AA09DB ON stu_systems (database_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_tachyon_scan (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, scan_time INTEGER NOT NULL, location_id INTEGER NOT NULL, CONSTRAINT FK_1ED54F94A76ED395 FOREIGN KEY (user_id) REFERENCES stu_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_1ED54F9464D218E FOREIGN KEY (location_id) REFERENCES stu_location (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_1ED54F9464D218E ON stu_tachyon_scan (location_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX tachyon_scan_user_idx ON stu_tachyon_scan (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_terraforming (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, description VARCHAR(255) NOT NULL, ecost INTEGER NOT NULL, v_feld INTEGER NOT NULL, z_feld INTEGER NOT NULL, duration INTEGER NOT NULL, research_id INTEGER DEFAULT NULL)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX terraforming_research_idx ON stu_terraforming (research_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX field_transformation_idx ON stu_terraforming (v_feld, z_feld)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_terraforming_cost (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, terraforming_id INTEGER NOT NULL, commodity_id INTEGER NOT NULL, count INTEGER NOT NULL, CONSTRAINT FK_4CD9B703B4ACC212 FOREIGN KEY (commodity_id) REFERENCES stu_commodity (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_4CD9B703BD31079C FOREIGN KEY (terraforming_id) REFERENCES stu_terraforming (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_4CD9B703B4ACC212 ON stu_terraforming_cost (commodity_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX terraforming_idx ON stu_terraforming_cost (terraforming_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_tholian_web (finished_time INTEGER DEFAULT NULL, id INTEGER NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_D032F9A0BF396750 FOREIGN KEY (id) REFERENCES stu_spacecraft (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_torpedo_cost (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, torpedo_type_id INTEGER NOT NULL, commodity_id INTEGER NOT NULL, count INTEGER NOT NULL, CONSTRAINT FK_AD0DEA44D12BAD4E FOREIGN KEY (torpedo_type_id) REFERENCES stu_torpedo_types (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_AD0DEA44B4ACC212 FOREIGN KEY (commodity_id) REFERENCES stu_commodity (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_AD0DEA44D12BAD4E ON stu_torpedo_cost (torpedo_type_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_AD0DEA44B4ACC212 ON stu_torpedo_cost (commodity_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_torpedo_hull (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, module_id INTEGER NOT NULL, torpedo_type INTEGER NOT NULL, modificator INTEGER NOT NULL, CONSTRAINT FK_B58BDD2B942323E3 FOREIGN KEY (torpedo_type) REFERENCES stu_torpedo_types (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_B58BDD2BAFC2B591 FOREIGN KEY (module_id) REFERENCES stu_modules (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX torpedo_hull_module_idx ON stu_torpedo_hull (module_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX torpedo_hull_torpedo_idx ON stu_torpedo_hull (torpedo_type)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_torpedo_storage (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, spacecraft_id INTEGER NOT NULL, torpedo_type INTEGER NOT NULL, CONSTRAINT FK_823719111C6AF6FD FOREIGN KEY (spacecraft_id) REFERENCES stu_spacecraft (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_82371911942323E3 FOREIGN KEY (torpedo_type) REFERENCES stu_torpedo_types (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_823719111C6AF6FD ON stu_torpedo_storage (spacecraft_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_82371911942323E3 ON stu_torpedo_storage (torpedo_type)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX torpedo_storage_spacecraft_idx ON stu_torpedo_storage (spacecraft_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_torpedo_types (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, base_damage INTEGER NOT NULL, critical_chance INTEGER NOT NULL, hit_factor INTEGER NOT NULL, hull_damage_factor INTEGER NOT NULL, shield_damage_factor INTEGER NOT NULL, variance INTEGER NOT NULL, commodity_id INTEGER NOT NULL, level INTEGER NOT NULL, research_id INTEGER NOT NULL, ecost INTEGER NOT NULL, amount INTEGER NOT NULL, CONSTRAINT FK_9C3F99F0B4ACC212 FOREIGN KEY (commodity_id) REFERENCES stu_commodity (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_9C3F99F0B4ACC212 ON stu_torpedo_types (commodity_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX torpedo_type_research_idx ON stu_torpedo_types (research_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX level_idx ON stu_torpedo_types (level)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_trade_license (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, posts_id INTEGER NOT NULL, user_id INTEGER NOT NULL, date INTEGER NOT NULL, expired INTEGER NOT NULL, CONSTRAINT FK_1A4E6AF5D5E258C5 FOREIGN KEY (posts_id) REFERENCES stu_trade_posts (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_1A4E6AF5A76ED395 FOREIGN KEY (user_id) REFERENCES stu_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_1A4E6AF5D5E258C5 ON stu_trade_license (posts_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_1A4E6AF5A76ED395 ON stu_trade_license (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX user_trade_post_idx ON stu_trade_license (user_id, posts_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_trade_license_info (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, posts_id INTEGER NOT NULL, commodity_id INTEGER NOT NULL, amount INTEGER NOT NULL, days INTEGER NOT NULL, date INTEGER NOT NULL, CONSTRAINT FK_88CAD317D5E258C5 FOREIGN KEY (posts_id) REFERENCES stu_trade_posts (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_88CAD317B4ACC212 FOREIGN KEY (commodity_id) REFERENCES stu_commodity (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_88CAD317B4ACC212 ON stu_trade_license_info (commodity_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX trade_license_info_post_idx ON stu_trade_license_info (posts_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_trade_offers (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, posts_id INTEGER NOT NULL, amount SMALLINT NOT NULL, wg_id INTEGER NOT NULL, wg_count INTEGER NOT NULL, gg_id INTEGER NOT NULL, gg_count INTEGER NOT NULL, date INTEGER NOT NULL, CONSTRAINT FK_4EB9B047D5E258C5 FOREIGN KEY (posts_id) REFERENCES stu_trade_posts (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_4EB9B047B221185A FOREIGN KEY (wg_id) REFERENCES stu_commodity (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_4EB9B047D2C18FD8 FOREIGN KEY (gg_id) REFERENCES stu_commodity (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_4EB9B047A76ED395 FOREIGN KEY (user_id) REFERENCES stu_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_4EB9B047D5E258C5 ON stu_trade_offers (posts_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_4EB9B047B221185A ON stu_trade_offers (wg_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_4EB9B047D2C18FD8 ON stu_trade_offers (gg_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_4EB9B047A76ED395 ON stu_trade_offers (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX trade_post_user_idx ON stu_trade_offers (posts_id, user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_trade_posts (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, name VARCHAR(255) NOT NULL, description CLOB NOT NULL, station_id INTEGER NOT NULL, trade_network SMALLINT NOT NULL, level SMALLINT NOT NULL, transfer_capacity INTEGER NOT NULL, storage INTEGER NOT NULL, is_dock_pm_auto_read BOOLEAN DEFAULT NULL, CONSTRAINT FK_13D25E73A76ED395 FOREIGN KEY (user_id) REFERENCES stu_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_13D25E7321BDB235 FOREIGN KEY (station_id) REFERENCES stu_station (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_13D25E73A76ED395 ON stu_trade_posts (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_13D25E7321BDB235 ON stu_trade_posts (station_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX trade_network_idx ON stu_trade_posts (trade_network)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX trade_post_station_idx ON stu_trade_posts (station_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_trade_shoutbox (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, trade_network_id SMALLINT NOT NULL, date INTEGER NOT NULL, message VARCHAR(255) NOT NULL, CONSTRAINT FK_8D70B786A76ED395 FOREIGN KEY (user_id) REFERENCES stu_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_8D70B786A76ED395 ON stu_trade_shoutbox (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX trade_network_date_idx ON stu_trade_shoutbox (trade_network_id, date)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_trade_transaction (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, wg_id INTEGER NOT NULL, wg_count INTEGER NOT NULL, gg_id INTEGER NOT NULL, gg_count INTEGER NOT NULL, date INTEGER NOT NULL, tradepost_id INTEGER DEFAULT NULL, CONSTRAINT FK_D2A0D0A2B221185A FOREIGN KEY (wg_id) REFERENCES stu_commodity (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_D2A0D0A2D2C18FD8 FOREIGN KEY (gg_id) REFERENCES stu_commodity (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_D2A0D0A2B221185A ON stu_trade_transaction (wg_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_D2A0D0A2D2C18FD8 ON stu_trade_transaction (gg_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX trade_transaction_date_tradepost_idx ON stu_trade_transaction (date, tradepost_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_trade_transfers (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, posts_id INTEGER NOT NULL, user_id INTEGER NOT NULL, count INTEGER NOT NULL, date INTEGER NOT NULL, CONSTRAINT FK_6556769DD5E258C5 FOREIGN KEY (posts_id) REFERENCES stu_trade_posts (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_6556769DA76ED395 FOREIGN KEY (user_id) REFERENCES stu_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6556769DD5E258C5 ON stu_trade_transfers (posts_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6556769DA76ED395 ON stu_trade_transfers (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX trade_transfer_post_user_idx ON stu_trade_transfers (posts_id, user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_trumfield (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, huelle INTEGER NOT NULL, former_rump_id INTEGER NOT NULL, location_id INTEGER NOT NULL, CONSTRAINT FK_3CBB9A4E64D218E FOREIGN KEY (location_id) REFERENCES stu_location (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3CBB9A4E64D218E ON stu_trumfield (location_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_tutorial_step (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, module VARCHAR(50) NOT NULL, "view" VARCHAR(100) NOT NULL, next_step_id INTEGER DEFAULT NULL, title CLOB DEFAULT NULL, text CLOB DEFAULT NULL, elementIds CLOB DEFAULT NULL, innerUpdate CLOB DEFAULT NULL, fallbackIndex INTEGER DEFAULT NULL, CONSTRAINT FK_82D9BF6BB13C343E FOREIGN KEY (next_step_id) REFERENCES stu_tutorial_step (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_82D9BF6BB13C343E ON stu_tutorial_step (next_step_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX tutorial_view_idx ON stu_tutorial_step (module, "view")
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, username VARCHAR(255) NOT NULL, allys_id INTEGER DEFAULT NULL, race INTEGER NOT NULL, state SMALLINT NOT NULL, lastaction INTEGER NOT NULL, kn_lez INTEGER NOT NULL, vac_active BOOLEAN NOT NULL, vac_request_date INTEGER NOT NULL, description CLOB NOT NULL, sessiondata CLOB NOT NULL, prestige INTEGER NOT NULL, deals BOOLEAN NOT NULL, last_boarding INTEGER DEFAULT NULL, CONSTRAINT FK_12A1701F5E0B0712 FOREIGN KEY (allys_id) REFERENCES stu_alliances (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_12A1701FDA6FBBAF FOREIGN KEY (race) REFERENCES stu_factions (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_12A1701FDA6FBBAF ON stu_user (race)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX user_alliance_idx ON stu_user (allys_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_user_award (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, award_id INTEGER NOT NULL, CONSTRAINT FK_E1449B84A76ED395 FOREIGN KEY (user_id) REFERENCES stu_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_E1449B843D5282CF FOREIGN KEY (award_id) REFERENCES stu_award (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E1449B84A76ED395 ON stu_user_award (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E1449B843D5282CF ON stu_user_award (award_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_user_character (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description CLOB DEFAULT NULL, avatar VARCHAR(32) DEFAULT NULL, former_user_id INTEGER DEFAULT NULL, user_id INTEGER DEFAULT NULL, CONSTRAINT FK_6E46626CA76ED395 FOREIGN KEY (user_id) REFERENCES stu_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6E46626CA76ED395 ON stu_user_character (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_user_invitations (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, invited_user_id INTEGER DEFAULT NULL, date DATETIME NOT NULL, token VARCHAR(255) NOT NULL)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX user_invitation_user_idx ON stu_user_invitations (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX user_invitation_token_idx ON stu_user_invitations (token)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_user_iptable (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, ip VARCHAR(255) NOT NULL, session VARCHAR(255) NOT NULL, agent VARCHAR(255) NOT NULL, startDate DATETIME DEFAULT NULL, endDate DATETIME DEFAULT NULL, CONSTRAINT FK_4DB0B7AAA76ED395 FOREIGN KEY (user_id) REFERENCES stu_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_4DB0B7AAA76ED395 ON stu_user_iptable (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX session_idx ON stu_user_iptable (session)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX iptable_start_idx ON stu_user_iptable (startDate)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX iptable_end_idx ON stu_user_iptable (endDate)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX iptable_ip_idx ON stu_user_iptable (ip)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_user_layer (map_type SMALLINT NOT NULL, user_id INTEGER NOT NULL, layer_id INTEGER NOT NULL, PRIMARY KEY(user_id, layer_id), CONSTRAINT FK_8FC49479A76ED395 FOREIGN KEY (user_id) REFERENCES stu_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_8FC49479EA6EFDCD FOREIGN KEY (layer_id) REFERENCES stu_layer (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_8FC49479A76ED395 ON stu_user_layer (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_8FC49479EA6EFDCD ON stu_user_layer (layer_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_user_lock (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER DEFAULT NULL, former_user_id INTEGER DEFAULT NULL, remaining_ticks INTEGER NOT NULL, reason CLOB NOT NULL, CONSTRAINT FK_F36B0C5EA76ED395 FOREIGN KEY (user_id) REFERENCES stu_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_F36B0C5EA76ED395 ON stu_user_lock (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX user_lock_user_idx ON stu_user_lock (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_user_map (cx INTEGER NOT NULL, cy INTEGER NOT NULL, map_id INTEGER NOT NULL, user_id INTEGER NOT NULL, layer_id INTEGER NOT NULL, PRIMARY KEY(cx, cy, user_id, layer_id), CONSTRAINT FK_C6E86038A76ED395 FOREIGN KEY (user_id) REFERENCES stu_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_C6E86038EA6EFDCD FOREIGN KEY (layer_id) REFERENCES stu_layer (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_C6E86038A76ED395 ON stu_user_map (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_C6E86038EA6EFDCD ON stu_user_map (layer_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_user_pirate_round (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, pirate_round_id INTEGER NOT NULL, destroyed_ships INTEGER NOT NULL, prestige INTEGER NOT NULL, CONSTRAINT FK_3CECD0A8A76ED395 FOREIGN KEY (user_id) REFERENCES stu_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_3CECD0A88DA7A3A1 FOREIGN KEY (pirate_round_id) REFERENCES stu_pirate_round (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3CECD0A8A76ED395 ON stu_user_pirate_round (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3CECD0A88DA7A3A1 ON stu_user_pirate_round (pirate_round_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_user_profile_visitors (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, recipient INTEGER NOT NULL, date INTEGER NOT NULL, CONSTRAINT FK_DD0F4487A76ED395 FOREIGN KEY (user_id) REFERENCES stu_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_DD0F44876804FB49 FOREIGN KEY (recipient) REFERENCES stu_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_DD0F44876804FB49 ON stu_user_profile_visitors (recipient)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX user_profile_visitor_user_idx ON stu_user_profile_visitors (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_user_referer (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, referer CLOB NOT NULL, user_id INTEGER NOT NULL, CONSTRAINT FK_A00722FDA76ED395 FOREIGN KEY (user_id) REFERENCES stu_user_registration (user_id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_A00722FDA76ED395 ON stu_user_referer (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_user_registration (login VARCHAR(20) NOT NULL, pass VARCHAR(255) NOT NULL, sms_code VARCHAR(6) DEFAULT NULL, email VARCHAR(200) NOT NULL, mobile VARCHAR(255) DEFAULT NULL, creation INTEGER NOT NULL, delmark SMALLINT NOT NULL, password_token VARCHAR(255) NOT NULL, sms_sended INTEGER DEFAULT 1, email_code VARCHAR(6) DEFAULT NULL, user_id INTEGER NOT NULL, PRIMARY KEY(user_id), CONSTRAINT FK_9C660348A76ED395 FOREIGN KEY (user_id) REFERENCES stu_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_user_setting (setting VARCHAR(255) NOT NULL, value VARCHAR(255) NOT NULL, user_id INTEGER NOT NULL, PRIMARY KEY(user_id, setting), CONSTRAINT FK_6AAFACE0A76ED395 FOREIGN KEY (user_id) REFERENCES stu_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6AAFACE0A76ED395 ON stu_user_setting (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_user_tag (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, tag_type_id INTEGER NOT NULL, date DATETIME NOT NULL, CONSTRAINT FK_56CC7D00A76ED395 FOREIGN KEY (user_id) REFERENCES stu_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_56CC7D00A76ED395 ON stu_user_tag (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_user_tutorial (user_id INTEGER NOT NULL, tutorial_step_id INTEGER NOT NULL, PRIMARY KEY(user_id, tutorial_step_id), CONSTRAINT FK_9840DDF3A76ED395 FOREIGN KEY (user_id) REFERENCES stu_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_9840DDF3D356979 FOREIGN KEY (tutorial_step_id) REFERENCES stu_tutorial_step (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_9840DDF3A76ED395 ON stu_user_tutorial (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_9840DDF3D356979 ON stu_user_tutorial (tutorial_step_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_weapon_shield (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, module_id INTEGER NOT NULL, weapon_id INTEGER NOT NULL, modificator INTEGER NOT NULL, faction_id INTEGER DEFAULT NULL, CONSTRAINT FK_9DC03BD595B82273 FOREIGN KEY (weapon_id) REFERENCES stu_weapons (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_9DC03BD5AFC2B591 FOREIGN KEY (module_id) REFERENCES stu_modules (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX weapon_shield_module_idx ON stu_weapon_shield (module_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX weapon_shield_weapon_idx ON stu_weapon_shield (weapon_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_weapons (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, variance SMALLINT NOT NULL, critical_chance SMALLINT NOT NULL, type SMALLINT NOT NULL, firing_mode SMALLINT NOT NULL, module_id INTEGER NOT NULL, CONSTRAINT FK_AB5A393AFC2B591 FOREIGN KEY (module_id) REFERENCES stu_modules (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_AB5A393AFC2B591 ON stu_weapons (module_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX weapon_module_idx ON stu_weapons (module_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_wormhole_entry (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, map_id INTEGER NOT NULL, system_id INTEGER NOT NULL, system_map_id INTEGER NOT NULL, type VARCHAR(10) NOT NULL, last_used INTEGER DEFAULT NULL, cooldown INTEGER DEFAULT NULL, CONSTRAINT FK_D68CF8C953C55F64 FOREIGN KEY (map_id) REFERENCES stu_map (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_D68CF8C9D0952FA5 FOREIGN KEY (system_id) REFERENCES stu_systems (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_D68CF8C9434BEAA5 FOREIGN KEY (system_map_id) REFERENCES stu_sys_map (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_D68CF8C953C55F64 ON stu_wormhole_entry (map_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_D68CF8C9D0952FA5 ON stu_wormhole_entry (system_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_D68CF8C9434BEAA5 ON stu_wormhole_entry (system_map_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_wormhole_restrictions (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, mode INTEGER DEFAULT NULL, wormhole_entry_id INTEGER DEFAULT NULL, user_id INTEGER DEFAULT NULL, CONSTRAINT FK_76C7B8E0BE56147A FOREIGN KEY (wormhole_entry_id) REFERENCES stu_wormhole_entry (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_76C7B8E0A76ED395 FOREIGN KEY (user_id) REFERENCES stu_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_76C7B8E0BE56147A ON stu_wormhole_restrictions (wormhole_entry_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_76C7B8E0A76ED395 ON stu_wormhole_restrictions (user_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP TABLE stu_alliance_boards
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_alliance_posts
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_alliance_settings
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_alliance_topics
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_alliances
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_alliances_jobs
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_alliances_relations
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_anomaly
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_anomaly_type
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_astro_entry
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_auction_bid
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_award
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_basic_trade
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_blocked_user
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_buildings
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_buildings_commodity
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_buildings_cost
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_buildings_field_alternative
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_buildings_functions
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_buildings_upgrades
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_buildings_upgrades_cost
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_buildplan
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_buildplans_hangar
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_buildplans_modules
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_buoy
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_colonies
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_colonies_classes
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_colonies_fielddata
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_colonies_shipqueue
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_colonies_shiprepair
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_colonies_terraforming
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_colony_class_deposit
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_colony_class_restriction
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_colony_deposit_mining
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_colony_fieldtype
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_colony_sandbox
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_colony_scan
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_commodity
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_construction_progress
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_contactlist
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_crew
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_crew_assign
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_crew_race
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_crew_training
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_database_categories
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_database_category_awards
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_database_entrys
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_database_types
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_database_user
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_deals
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_dockingrights
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_factions
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_field_build
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_fleets
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_flight_sig
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_game_config
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_game_request
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_game_turn_stats
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_game_turns
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_history
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_ignorelist
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_kn
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_kn_archiv
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_kn_character
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_kn_comments
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_kn_comments_archiv
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_kn_plot_application
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_layer
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_location
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_location_mining
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_lottery_buildplan
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_lottery_ticket
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_map
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_map_bordertypes
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_map_ftypes
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_map_regions
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_map_regions_settlement
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_mass_center_type
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_mining_queue
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_modules
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_modules_buildingfunction
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_modules_cost
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_modules_queue
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_modules_specials
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_names
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_news
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_notes
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_npc_log
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_opened_advent_door
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_partnersite
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_pirate_round
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_pirate_setup
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_pirate_setup_buildplan
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_pirate_wrath
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_planet_type_research
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_planets_commodity
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_plots
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_plots_archiv
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_plots_members
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_plots_members_archiv
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_pm_cats
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_pms
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_prestige_log
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_progress_module
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_repair_task
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_research
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_research_dependencies
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_researched
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_rump
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_rump_costs
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_rumps_buildingfunction
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_rumps_cat_role_crew
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_rumps_categories
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_rumps_colonize_building
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_rumps_module_level
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_rumps_module_special
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_rumps_roles
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_rumps_specials
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_rumps_user
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_session_strings
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_ship
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_ship_log
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_ship_takeover
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_shipyard_shipqueue
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_spacecraft
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_spacecraft_condition
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_spacecraft_emergency
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_spacecraft_system
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_station
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_station_shiprepair
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_storage
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_sys_map
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_system_types
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_systems
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_tachyon_scan
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_terraforming
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_terraforming_cost
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_tholian_web
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_torpedo_cost
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_torpedo_hull
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_torpedo_storage
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_torpedo_types
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_trade_license
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_trade_license_info
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_trade_offers
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_trade_posts
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_trade_shoutbox
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_trade_transaction
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_trade_transfers
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_trumfield
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_tutorial_step
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_user
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_user_award
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_user_character
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_user_invitations
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_user_iptable
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_user_layer
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_user_lock
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_user_map
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_user_pirate_round
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_user_profile_visitors
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_user_referer
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_user_registration
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_user_setting
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_user_tag
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_user_tutorial
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_weapon_shield
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_weapons
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_wormhole_entry
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_wormhole_restrictions
        SQL);
    }
}
