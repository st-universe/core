<?php

declare(strict_types=1);

namespace Stu\Action;

use Doctrine\ORM\EntityManagerInterface;
use request;
use Stu\ActionTestCase;
use Stu\Module\Admin\Action\CreateRump;
use Stu\Module\Control\GameControllerInterface;

final class CreateRumpTest extends ActionTestCase
{
    public function testCreatesRumpAndAllSelectedConfigurations(): void
    {
        $container = $this->getContainer();
        $connection = $container->get(EntityManagerInterface::class)->getConnection();
        $connection->insert('stu_database_entrys', [
            'description' => 'Lückenloser Testeintrag',
            'data' => '',
            'category_id' => 1,
            'type' => 1,
            'sort' => 21,
            'object_id' => 99991
        ]);
        $contiguousEntryId = (int) $connection->lastInsertId();
        $connection->insert('stu_database_entrys', [
            'description' => 'Eintrag nach der Lücke',
            'data' => '',
            'category_id' => 1,
            'type' => 1,
            'sort' => 23,
            'object_id' => 99992
        ]);
        $entryAfterGapId = (int) $connection->lastInsertId();
        self::$testSession->setUserById(102);
        request::setMockVars([
            'rump_id' => '19999',
            'rump_name' => 'Creator Testrumpf',
            'category_id' => '6',
            'role_id' => '5',
            'faction_id' => '1',
            'commodity_id' => '2',
            'sort' => '19999',
            'is_buildable' => '1',
            'is_npc' => '1',
            'npc_buildable' => '1',
            'base_torpedo_storage' => '3',
            'phaser_volleys' => '4',
            'phaser_hull_damage_factor' => '5',
            'phaser_shield_damage_factor' => '6',
            'torpedo_level' => '7',
            'torpedo_volleys' => '8',
            'eps_cost' => '9',
            'storage' => '10',
            'slots' => '11',
            'buildtime' => '12',
            'needed_workbees' => '13',
            'flight_ecost' => '14',
            'beam_factor' => '15',
            'shuttle_slots' => '16',
            'tractor_mass' => '17',
            'tractor_payload' => '18',
            'prestige' => '-19',
            'evade_chance' => '20',
            'hit_chance' => '21',
            'module_level' => '22',
            'base_crew' => '23',
            'max_crew' => '24',
            'base_eps' => '25',
            'base_reactor' => '26',
            'base_hull' => '27',
            'base_shield' => '28',
            'base_damage' => '29',
            'base_sensor_range' => '30',
            'base_warpdrive' => '31',
            'special_slots' => '32',
            'create_module_levels' => '1',
            'module_min_1' => '1',
            'module_default_1' => '2',
            'module_max_1' => '3',
            'module_mandatory_1' => '1',
            'module_min_2' => '1',
            'module_default_2' => '2',
            'module_max_2' => '3',
            'module_mandatory_2' => '1',
            'module_min_3' => '1',
            'module_default_3' => '2',
            'module_max_3' => '3',
            'module_mandatory_3' => '1',
            'module_min_4' => '1',
            'module_default_4' => '2',
            'module_max_4' => '3',
            'module_mandatory_4' => '1',
            'module_min_5' => '1',
            'module_default_5' => '2',
            'module_max_5' => '3',
            'module_mandatory_5' => '1',
            'module_min_6' => '1',
            'module_default_6' => '2',
            'module_max_6' => '3',
            'module_mandatory_6' => '1',
            'module_min_7' => '1',
            'module_default_7' => '2',
            'module_max_7' => '3',
            'module_mandatory_7' => '1',
            'module_min_8' => '1',
            'module_default_8' => '2',
            'module_max_8' => '3',
            'module_mandatory_8' => '1',
            'module_min_10' => '1',
            'module_default_10' => '2',
            'module_max_10' => '3',
            'module_mandatory_10' => '1',
            'module_min_11' => '1',
            'module_default_11' => '2',
            'module_max_11' => '3',
            'module_mandatory_11' => '1',
            'cost_commodity_ids' => ['2'],
            'cost_amounts' => ['33'],
            'module_special_ids' => ['1'],
            'building_function_ids' => ['1'],
            'special_ability_ids' => ['1'],
            'colonization_building_id' => '12345',
            'create_model_3d' => '1',
            'model_3d_width' => '34',
            'model_3d_height' => '35',
            'model_3d_rotation' => '36',
            'create_database_entry' => '1',
            'database_entry_id' => '59999999',
            'database_entry_description' => 'Creator Testrumpf Datenbank',
            'database_entry_data' => 'Datenbanktext',
            'database_entry_category_id' => '1',
            'database_entry_before_id' => '6501001'
        ]);

        $container->get(CreateRump::class)->handle($container->get(GameControllerInterface::class));

        $rump = $connection->fetchAssociative('SELECT * FROM stu_rump WHERE id = ?', [19999]);
        $baseValues = $connection->fetchAssociative('SELECT * FROM stu_rump_base_values WHERE rump_id = ?', [19999]);
        $moduleLevels = $connection->fetchOne('SELECT type_values FROM stu_rump_module_level WHERE rump_id = ?', [19999]);
        $databaseEntry = $connection->fetchAssociative('SELECT * FROM stu_database_entrys WHERE object_id = ?', [19999]);

        self::assertSame('Creator Testrumpf', $rump['name']);
        self::assertSame('6', (string) $rump['category_id']);
        self::assertSame('5', (string) $rump['role_id']);
        self::assertSame('2', (string) $rump['commodity_id']);
        self::assertSame('-19', (string) $rump['prestige']);
        self::assertSame('27', (string) $baseValues['base_hull']);
        self::assertSame('32', (string) $baseValues['special_slots']);
        self::assertSame(2, json_decode((string) $moduleLevels, true, flags: JSON_THROW_ON_ERROR)['1']['default']);
        self::assertSame('Creator Testrumpf Datenbank', $databaseEntry['description']);
        self::assertSame('59999999', (string) $databaseEntry['id']);
        self::assertSame('19999', (string) $databaseEntry['object_id']);
        self::assertSame('20', (string) $databaseEntry['sort']);
        self::assertSame('21', (string) $connection->fetchOne('SELECT sort FROM stu_database_entrys WHERE id = ?', [6501001]));
        self::assertSame('22', (string) $connection->fetchOne('SELECT sort FROM stu_database_entrys WHERE id = ?', [$contiguousEntryId]));
        self::assertSame('23', (string) $connection->fetchOne('SELECT sort FROM stu_database_entrys WHERE id = ?', [$entryAfterGapId]));
        self::assertSame((string) $databaseEntry['id'], (string) $rump['database_id']);
        self::assertSame('33', (string) $connection->fetchOne('SELECT count FROM stu_rump_costs WHERE rump_id = ? AND commodity_id = ?', [19999, 2]));
        self::assertSame('1', (string) $connection->fetchOne('SELECT module_special_id FROM stu_rumps_module_special WHERE rump_id = ?', [19999]));
        self::assertSame('1', (string) $connection->fetchOne('SELECT building_function FROM stu_rumps_buildingfunction WHERE rump_id = ?', [19999]));
        self::assertSame('1', (string) $connection->fetchOne('SELECT special FROM stu_rumps_specials WHERE rump_id = ?', [19999]));
        self::assertSame('12345', (string) $connection->fetchOne('SELECT building_id FROM stu_rumps_colonize_building WHERE rump_id = ?', [19999]));
        self::assertSame('34', (string) $connection->fetchOne('SELECT width FROM stu_rumps_3d_model WHERE rump_id = ?', [19999]));

        request::setMockVars([
            'B_UPDATE_RUMP' => '1',
            'edit_rump_id' => '19999',
            'rump_id' => '19999',
            'rump_name' => 'Bearbeiteter Creator Testrumpf',
            'category_id' => '6',
            'role_id' => '5',
            'faction_id' => '1',
            'commodity_id' => '2',
            'sort' => '20000',
            'is_buildable' => '1',
            'npc_buildable' => '0',
            'base_torpedo_storage' => '1',
            'phaser_volleys' => '2',
            'phaser_hull_damage_factor' => '3',
            'phaser_shield_damage_factor' => '4',
            'torpedo_level' => '5',
            'torpedo_volleys' => '6',
            'eps_cost' => '7',
            'storage' => '8',
            'slots' => '9',
            'buildtime' => '10',
            'needed_workbees' => '',
            'flight_ecost' => '11',
            'beam_factor' => '12',
            'shuttle_slots' => '13',
            'tractor_mass' => '14',
            'tractor_payload' => '15',
            'prestige' => '16',
            'evade_chance' => '17',
            'hit_chance' => '18',
            'module_level' => '19',
            'base_crew' => '20',
            'max_crew' => '21',
            'base_eps' => '22',
            'base_reactor' => '23',
            'base_hull' => '24',
            'base_shield' => '25',
            'base_damage' => '26',
            'base_sensor_range' => '27',
            'base_warpdrive' => '28',
            'special_slots' => '29',
            'database_id' => '59999999',
            'update_database_entry' => '1',
            'database_entry_description' => 'Bearbeiteter Datenbankeintrag',
            'database_entry_data' => 'Bearbeiteter Datenbanktext',
            'database_entry_category_id' => '1',
            'database_entry_before_id' => ''
        ]);

        $container->get(CreateRump::class)->handle($container->get(GameControllerInterface::class));

        self::assertSame('Bearbeiteter Creator Testrumpf', $connection->fetchOne('SELECT name FROM stu_rump WHERE id = ?', [19999]));
        self::assertSame('16', (string) $connection->fetchOne('SELECT prestige FROM stu_rump WHERE id = ?', [19999]));
        self::assertSame('24', (string) $connection->fetchOne('SELECT base_hull FROM stu_rump_base_values WHERE rump_id = ?', [19999]));
        self::assertSame('Bearbeiteter Datenbankeintrag', $connection->fetchOne('SELECT description FROM stu_database_entrys WHERE id = ?', [59999999]));
        self::assertSame('Bearbeiteter Datenbanktext', $connection->fetchOne('SELECT data FROM stu_database_entrys WHERE id = ?', [59999999]));
        self::assertFalse((bool) $connection->fetchOne('SELECT COUNT(*) FROM stu_rump_module_level WHERE rump_id = ?', [19999]));
        self::assertFalse((bool) $connection->fetchOne('SELECT COUNT(*) FROM stu_rumps_3d_model WHERE rump_id = ?', [19999]));
        self::assertFalse((bool) $connection->fetchOne('SELECT COUNT(*) FROM stu_rump_costs WHERE rump_id = ?', [19999]));
        self::assertFalse((bool) $connection->fetchOne('SELECT COUNT(*) FROM stu_rumps_module_special WHERE rump_id = ?', [19999]));
        self::assertFalse((bool) $connection->fetchOne('SELECT COUNT(*) FROM stu_rumps_buildingfunction WHERE rump_id = ?', [19999]));
        self::assertFalse((bool) $connection->fetchOne('SELECT COUNT(*) FROM stu_rumps_specials WHERE rump_id = ?', [19999]));
        self::assertFalse((bool) $connection->fetchOne('SELECT COUNT(*) FROM stu_rumps_colonize_building WHERE rump_id = ?', [19999]));
    }
}
