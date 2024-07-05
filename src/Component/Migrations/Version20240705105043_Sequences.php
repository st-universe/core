<?php

declare(strict_types=1);

namespace Stu\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240705105043_Sequences extends AbstractMigration
{
    private const array TABLE_NAMES = [
        'stu_alliance_boards',
        'stu_alliance_posts',
        'stu_alliance_topics',
        'stu_alliances',
        'stu_alliances_jobs',
        'stu_alliances_relations',
        'stu_anomaly',
        'stu_astro_entry',
        'stu_auction_bid',
        'stu_basic_trade',
        'stu_buildings',
        'stu_buildings_commodity',
        'stu_buildings_cost',
        'stu_buildings_field_alternative',
        'stu_buildings_functions',
        'stu_buildings_upgrades',
        'stu_buildings_upgrades_cost',
        'stu_buildplans',
        'stu_buildplans_hangar',
        'stu_buildplans_modules',
        'stu_buoy',
        'stu_colonies',
        'stu_colonies_classes',
        'stu_colonies_fielddata',
        'stu_colonies_shipqueue',
        'stu_colonies_shiprepair',
        'stu_colonies_terraforming',
        'stu_colony_fieldtype',
        'stu_colony_sandbox',
        'stu_colony_scan',
        'stu_commodity',
        'stu_construction_progress',
        'stu_contactlist',
        'stu_crew',
        'stu_crew_assign',
        'stu_crew_race',
        'stu_crew_training',
        'stu_database_categories',
        'stu_database_entrys',
        'stu_database_types',
        'stu_database_user',
        'stu_deals',
        'stu_dockingrights',
        'stu_factions',
        'stu_field_build',
        'stu_fleets',
        'stu_flight_sig',
        'stu_game_config',
        'stu_game_request',
        'stu_game_turn_stats',
        'stu_game_turns',
        'stu_history',
        'stu_ignorelist',
        'stu_kn',
        'stu_kn_archiv',
        'stu_kn_characters',
        'stu_kn_comments',
        'stu_kn_comments_archiv',
        'stu_kn_plot_application',
        'stu_layer',
        'stu_lottery_ticket',
        'stu_map',
        'stu_map_bordertypes',
        'stu_map_ftypes',
        'stu_map_regions',
        'stu_map_regions_settlement',
        'stu_mass_center_type',
        'stu_modules',
        'stu_modules_buildingfunction',
        'stu_modules_cost',
        'stu_modules_queue',
        'stu_modules_specials',
        'stu_names',
        'stu_news',
        'stu_notes',
        'stu_npc_log',
        'stu_opened_advent_door',
        'stu_partnersite',
        'stu_pirate_setup',
        'stu_planet_type_research',
        'stu_planets_commodity',
        'stu_plots',
        'stu_plots_archiv',
        'stu_plots_members',
        'stu_plots_members_archiv',
        'stu_pm_cats',
        'stu_pms',
        'stu_prestige_log',
        'stu_progress_module',
        'stu_repair_task',
        'stu_research',
        'stu_research_dependencies',
        'stu_researched',
        'stu_rump_costs',
        'stu_rumps',
        'stu_rumps_buildingfunction',
        'stu_rumps_cat_role_crew',
        'stu_rumps_categories',
        'stu_rumps_colonize_building',
        'stu_rumps_module_level',
        'stu_rumps_module_special',
        'stu_rumps_roles',
        'stu_rumps_specials',
        'stu_rumps_user',
        'stu_session_strings',
        'stu_ship_log',
        'stu_ship_system',
        'stu_ship_takeover',
        'stu_ships',
        'stu_shipyard_shipqueue',
        'stu_spacecraft_emergency',
        'stu_station_shiprepair',
        'stu_storage',
        'stu_sys_map',
        'stu_system_types',
        'stu_systems',
        'stu_tachyon_scan',
        'stu_terraforming',
        'stu_terraforming_cost',
        'stu_tholian_web',
        'stu_torpedo_cost',
        'stu_torpedo_hull',
        'stu_torpedo_storage',
        'stu_torpedo_types',
        'stu_trade_license',
        'stu_trade_license_info',
        'stu_trade_offers',
        'stu_trade_posts',
        'stu_trade_shoutbox',
        'stu_trade_transaction',
        'stu_trade_transfers',
        'stu_user',
        'stu_user_award',
        'stu_user_characters',
        'stu_user_invitations',
        'stu_user_iptable',
        'stu_user_lock',
        'stu_user_profile_visitors',
        'stu_user_tag',
        'stu_weapon_shield',
        'stu_weapons',
        'stu_wormhole_entry'
    ];

    public function getDescription(): string
    {
        return 'Modifies Entity IDs from default nextval() to DEFAULT IDENTITY';
    }

    public function up(Schema $schema): void
    {
        foreach (self::TABLE_NAMES as $table) {
            $this->dropTableIdDefault($table);
            $this->renameOldSequences($table);
            $this->addDefaultGeneratedIdentity($table);
            $this->resetOldSequenceValues($table);
            $this->dropOldSequences($table);
        }
    }

    private function dropTableIdDefault(string $table): void
    {
        $this->addSql(sprintf('ALTER TABLE %s ALTER id DROP DEFAULT', $table));
    }

    private function renameOldSequences(string $table): void
    {
        $this->addSql(sprintf('ALTER SEQUENCE %1$s_id_seq RENAME TO %1$s_id_seq_alt', $table));
    }

    private function addDefaultGeneratedIdentity(string $table): void
    {
        $this->addSql(sprintf('ALTER TABLE %s ALTER id ADD GENERATED BY DEFAULT AS IDENTITY', $table));
    }

    private function resetOldSequenceValues(string $table): void
    {
        $this->addSql(sprintf('SELECT SETVAL(\'%1$s_id_seq\', (SELECT last_value FROM %1$s_id_seq_alt))', $table));
    }

    private function dropOldSequences(string $table): void
    {
        $this->addSql(sprintf('DROP SEQUENCE %s_id_seq_alt', $table));
    }

    public function down(Schema $schema): void
    {
        foreach (self::TABLE_NAMES as $table) {
            $this->createTempSequence($table);
            $this->setTempSequenceValue($table);
            $this->dropIdentity($table);
            $this->renameTempSequence($table);
            $this->setIdDefault($table);
        }
    }

    private function createTempSequence(string $table): void
    {
        $this->addSql(sprintf('CREATE SEQUENCE %1$s_id_seq_temp', $table));
    }

    private function setTempSequenceValue(string $table): void
    {
        $this->addSql(sprintf('SELECT SETVAL(\'%1$s_id_seq_temp\', (SELECT last_value FROM %1$s_id_seq))', $table));
    }

    private function dropIdentity(string $table): void
    {
        $this->addSql(sprintf('ALTER TABLE %1$s ALTER id DROP IDENTITY', $table));
    }

    private function renameTempSequence(string $table): void
    {
        $this->addSql(sprintf('ALTER SEQUENCE %1$s_id_seq_temp RENAME TO %1$s_id_seq', $table));
    }

    private function setIdDefault(string $table): void
    {
        $this->addSql(sprintf('ALTER TABLE %1$s ALTER COLUMN id SET DEFAULT nextval(\'%1$s_id_seq\'::regclass)', $table));
    }
}
