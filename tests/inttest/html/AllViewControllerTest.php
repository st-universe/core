<?php

declare(strict_types=1);

namespace Stu\Html;

use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;
use Stu\Config\Init;
use Stu\Lib\Map\VisualPanel\Layer\PanelLayerCreation;
use Stu\Lib\Map\VisualPanel\Layer\PanelLayerEnum;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Game\Component\GameComponentEnum;
use Stu\StuMocks;
use Stu\TwigTestCase;

class AllViewControllerTest extends TwigTestCase
{
    private const array CURRENTLY_UNSUPPORTED_MODULES = [
        'STATION_VIEWS'                             // has own test case
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
        'COLONY_VIEWS-SHOW_MODULE_CANCEL',          // needs $module = $game->getViewContext(ViewContextTypeEnum::MODULE);
        'COLONY_VIEWS-SHOW_PODS_LOCATIONS',
        'COLONY_VIEWS-SHOW_SPACECRAFTSTORAGE',      // duplication of SPACECRAFT_VIEWS-SHOW_SPACECRAFTSTORAGE
        'COLONY_VIEWS-SHOW_SUBSPACE_TELESCOPE',     // needs corresponding building on colony
        'COLONY_VIEWS-SHOW_TELESCOPE_SCAN',         // needs corresponding building on colony
        'DATABASE_VIEWS-SHOW_SATISFIED_WORKER',
        'DATABASE_VIEWS-SHOW_STATISTICS',
        'DATABASE_VIEWS-SHOW_SURFACE',              // needs surface scan test data
        'DATABASE_VIEWS-SHOW_TOP_RPG',              // sql in getRpgVotesTop10 not compatible with sqlite?
        'GAME_VIEWS-SHOW_COMPONENT',                // make separate tests for each ComponentEnum
        'GAME_VIEWS-SHOW_INNER_CONTENT',            // make separate tests for each ModuleEnum
        'MAINDESK_VIEWS-SHOW_COLONYLIST',           // needs uncolonized user
        'PM_VIEWS-SHOW_CONTACT_MODESWITCH',
        'USERPROFILE_VIEWS-SHOW_SURFACE',           // needs surface scan test data
        'SHIP_VIEWS-SHOW_ASTRO_ENTRY',              // needs astro entry
        'SHIP_VIEWS-SHOW_BUSSARD_COLLECTOR_AJAX',   // needs corresponding ship system
        'SHIP_VIEWS-SHOW_COLONIZATION',             // needs colonizer ship over free colony
        'SPACECRAFT_VIEWS-SHOW_COLONY_SCAN',        // needs ship over colony with matrix scanner
        'SPACECRAFT_VIEWS-SHOW_RENAME_CREW',
        'SPACECRAFT_VIEWS-SHOW_SCAN',               // needs ship on same location
        'SPACECRAFT_VIEWS-SHOW_SECTOR_SCAN',        // not idempotent, because it creates prestige log
        'SPACECRAFT_VIEWS-SHOW_SPACECRAFT',         // has own test case
        'SPACECRAFT_VIEWS-SHOW_SYSTEM_SETTINGS_AJAX',  // has own test case
        'SPACECRAFT_VIEWS-SHOW_TRANSFER',           // has own test case
        'SPACECRAFT_VIEWS-SHOW_WASTEMENU',
        'TRADE_VIEWS-SHOW_OFFER_MENU',
        'TRADE_VIEWS-SHOW_OFFER_MENU_TRANSFER',
        'TRADE_VIEWS-SHOW_OFFER_MENU_NEW_OFFER',
        'TRADE_VIEWS-SHOW_TAKE_OFFER',
        'TRADE_VIEWS-SHOW_TRADEPOST_INFO',          // DEAD LOCK
    ];

    private string $snapshotKey = '';

    public static function setUpBeforeClass(): void
    {
        StuMocks::get()->registerStubbedComponent(GameComponentEnum::COLONIES)
            ->registerStubbedComponent(GameComponentEnum::NAVIGATION)
            ->registerStubbedComponent(GameComponentEnum::PM)
            ->registerStubbedComponent(GameComponentEnum::RESEARCH)
            ->registerStubbedComponent(GameComponentEnum::SERVERTIME_AND_VERSION)
            ->registerStubbedComponent(GameComponentEnum::USER);
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
            ->filter(fn(array $array): bool => !str_ends_with($array[0], '-DEFAULT_VIEW')) // got its own test: DefaultViewsControllerTest.php
            ->filter(fn(array $array): bool => !in_array($array[0], self::CURRENTLY_UNSUPPORTED_KEYS))
            ->filter(fn(array $array): bool => array_filter(self::CURRENTLY_UNSUPPORTED_MODULES, fn(string $unsupportedModule) => str_starts_with($array[0], $unsupportedModule)) == [])
            ->toArray();
    }

    #[DataProvider('getAllViewControllerDataProvider')]
    public function testHandle(string $key): void
    {
        PanelLayerCreation::$skippedLayers[] = PanelLayerEnum::ANOMALIES->value;

        $this->snapshotKey = $key;

        $this->renderSnapshot(
            101,
            Init::getContainer()
                ->getDefinedImplementationsOf(ViewControllerInterface::class, true)->get($key),
            $this->getGeneralRequestVariables()
        );
    }

    private function getGeneralRequestVariables(): array
    {
        return [
            'id' => 42,
            'target' => 43,
            'userid' => 101,
            'factionid' => 1,
            'layerid' => 2,
            'section' => 1,
            'systemid' => 252,
            'x' => 5,
            'y' => 5,
            'hosttype' => 1,
            'commodityid' => 21,
            'noteid' => 42,
            'regionid' => 134,
            'switch' => 1,

            // SHIP
            'rumpid' => 6501,
            'planid' => 2324,
            'shipid' => 42,
            'fleetid' => 77,
            'buoyid' => 42,

            // COLONY
            'fid' => 26,
            'func' => 87,
            'buildingid' => 82010100,
            'shuttletarget' => 77,

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
            'cat' => 1,

            // TRADE
            'postid' => '2',
            'mode' => 'to',
            'network' => '101'
        ];
    }
}
