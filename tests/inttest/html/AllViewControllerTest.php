<?php

declare(strict_types=1);

namespace Stu\Html;

use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;
use Stu\Config\Init;
use Stu\Module\Control\ViewControllerInterface;
use Stu\TwigTestCase;

class AllViewControllerTest extends TwigTestCase
{
    private const array CURRENTLY_UNSUPPORTED_MODULES = [
        'TRADE_VIEWS',                              // needs tradepost
        'STATION_VIEWS'                             // needs station
    ];

    private const array CURRENTLY_UNSUPPORTED_KEYS = [
        'ADMIN_VIEWS-NOOP',
        'ADMIN_VIEWS-ADMIN_SHOW_SIGNATURES',
        'ADMIN_VIEWS-SHOW_COLONY_SANDBOX',
        'ADMIN_VIEWS-SHOW_INFLUENCE_AREAS',
        'ADMIN_VIEWS-SHOW_MAP_OVERALL',
        'ADMIN_VIEWS-SHOW_RESEARCH_TREE',
        'ALLIANCE_VIEWS-CREATE_ALLIANCE',
        'ALLIANCE_VIEWS-SHOW_DIPLOMATIC_RELATIONS',
        'ALLIANCE_VIEWS-SHOW_EDIT_ALLY_POST',
        'COLONY_VIEWS-SHOW_BEAMFROM',               // needs ship in orbit
        'COLONY_VIEWS-SHOW_BEAMTO',                 // needs ship in orbit
        'COLONY_VIEWS-SHOW_MODULE_CANCEL',          // needs $module = $game->getViewContext(ViewContextTypeEnum::MODULE);
        'COLONY_VIEWS-SHOW_PODS_LOCATIONS',
        'COLONY_VIEWS-SHOW_SHUTTLE_MANAGEMENT',     // needs ship with shuttle module in orbit
        'COLONY_VIEWS-SHOW_SUBSPACE_TELESCOPE',     // needs corresponding building on colony
        'COLONY_VIEWS-SHOW_TELESCOPE_SCAN',         // needs corresponding building on colony
        'DATABASE_VIEWS-SHOW_SATISFIED_WORKER',
        'DATABASE_VIEWS-SHOW_STATISTICS',
        'DATABASE_VIEWS-SHOW_SURFACE',              // needs surface scan test data
        'DATABASE_VIEWS-SHOW_TOP_RPG',              // sql in getRpgVotesTop10 not compatible with sqlite?
        'GAME_VIEWS-SHOW_COMPONENT',                // make separate tests for each ComponentEnum
        'GAME_VIEWS-SHOW_INNER_CONTENT',            // make separate tests for each ModuleViewEnum
        'MAINDESK_VIEWS-SHOW_COLONYLIST',           // needs uncolonized user
        'MESSAGE_VIEWS-SHOW_CONTACT_MODESWITCH',
        'PLAYER_PROFILE_VIEWS-SHOW_SURFACE',        // needs surface scan test data
        'SHIP_VIEWS-SHOW_AGGREGATION_SYSTEM_AJAX',  // needs corresponding system
        'SHIP_VIEWS-SHOW_ASTRO_ENTRY',              // needs astro entry
        'SHIP_VIEWS-SHOW_AVAILABLE_SHIPS',          // needs fleet and ships on location
        'SHIP_VIEWS-SHOW_BUSSARD_COLLECTOR_AJAX',   // needs corresponding ship system
        'SHIP_VIEWS-SHOW_COLONIZATION',             // needs colonizer ship over free colony
        'SHIP_VIEWS-SHOW_COLONY_SCAN',              // needs ship over colony with matrix scanner
        'SHIP_VIEWS-SHOW_ETRANSFER',                // needs ship on same location
        'SHIP_VIEWS-SHOW_RENAME_CREW',
        'SHIP_VIEWS-SHOW_SCAN',                     // needs ship on same location
        'SHIP_VIEWS-SHOW_TRANSFER',                 // needs ship or colony on same location
        'SHIP_VIEWS-SHOW_SHIP',                     // has own test case
        'SHIP_VIEWS-SHOW_SHIPLIST_FLEET',           // needs fleet
        'SHIP_VIEWS-SHOW_TRADEMENU',
        'SHIP_VIEWS-SHOW_TRADEMENU_CHOOSE_PAYMENT', // needs tradepost
        'SHIP_VIEWS-SHOW_TRADEMENU_TRANSFER',       // needs tradepost on ship location
        'SHIP_VIEWS-SHOW_WEBEMITTER_AJAX',          // needs web emitter module
    ];

    private string $snapshotKey = '';

    #[Override]
    protected function getViewControllerClass(): string
    {
        return 'PROVIDED_BY_DATA_PROVIDER';
    }

    #[Override]
    protected function getSnapshotId(): string
    {
        return (new ReflectionClass($this))->getShortName() . '--' .
            $this->snapshotKey;
    }

    public static function getAllViewControllerDataProvider(): array
    {
        $definedImplementations =  Init::getContainer()
            ->getDefinedImplementationsOf(ViewControllerInterface::class, true);

        return $definedImplementations
            ->map(fn(ViewControllerInterface $viewController): array => [$definedImplementations->indexOf($viewController)])
            //->filter(fn(array $array): bool => $array[0] === 'SHIP_VIEWS-SHOW_ANALYSE_BUOY')
            ->filter(fn(array $array): bool => !str_ends_with($array[0], '-DEFAULT_VIEW') || str_starts_with($array[0], 'NPC_VIEWS') || str_starts_with($array[0], 'ADMIN_VIEWS'))
            ->filter(fn(array $array): bool => !in_array($array[0], self::CURRENTLY_UNSUPPORTED_KEYS))
            ->filter(fn(array $array): bool => array_filter(self::CURRENTLY_UNSUPPORTED_MODULES, fn(string $unsupportedModule) => str_starts_with($array[0], $unsupportedModule)) == [])
            ->toArray();
    }

    #[DataProvider('getAllViewControllerDataProvider')]
    public function testHandle(string $key): void
    {
        $this->snapshotKey = $key;

        $this->renderSnapshot(
            $this->getGeneralRequestVariables(),
            Init::getContainer()
                ->getDefinedImplementationsOf(ViewControllerInterface::class, true)->get($key)
        );
    }

    private function getGeneralRequestVariables(): array
    {
        return [
            'id' => 42,
            'userid' => 101,
            'factionid' => 1,
            'layerid' => 2,
            'section' => 1,
            'systemid' => 252,
            'x' => 5,
            'y' => 5,
            'hosttype' => 1,
            'commodityId' => 21,
            'noteid' => 42,
            'regionid' => 134,

            // SHIP
            'rumpid' => 6501,
            'planid' => 2324,
            'shipid' => 42,

            // COLONY
            'fid' => 26,
            'func' => 87,
            'buildingid' => 82010100,

            // MAP
            'macro' => 'html/map/starmapSectionTable.twig',

            // ALLIANCE
            'boardid' => 1,
            'topicid' => 1,

            // COMMUNICATION
            'knid' => 42,
            'plotid' => 9,
            'character' => 42,
            'contactid' => 102,
            'pmcat' => 4780,
            'fromid' => 42,
            'toid' => 42,
            'fromtype' => 2,
            'totype' => 5,

            // DATABASE
            'ent' => 6501001,
            'cat' => 1
        ];
    }
}
