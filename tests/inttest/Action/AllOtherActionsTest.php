<?php

declare(strict_types=1);

namespace Stu\Action;

use PHPUnit\Framework\Attributes\DataProvider;
use request;
use Stu\ActionTestCase;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Component\Building\BuildMenuEnum;
use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Config\Init;
use Stu\Lib\Transfer\TransferTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;

class AllOtherActionsTest extends ActionTestCase
{
    private const array CURRENTLY_SUPPORTED_MODULES = [
        'COLONY_ACTIONS',
        'SHIP_ACTIONS',
        'SPACECRAFT_ACTIONS',
        'TRADE_ACTIONS'
    ];

    private const array CURRENTLY_UNSUPPORTED_KEYS = [];

    public static function getAllActionControllerDataProvider(): array
    {
        $definedImplementations =  Init::getContainer()
            ->getDefinedImplementationsOf(ActionControllerInterface::class, true);

        return $definedImplementations
            ->map(fn(ActionControllerInterface $actionController): array => [$definedImplementations->indexOf($actionController)])
            ->filter(fn(array $array): bool => !in_array($array[0], self::CURRENTLY_UNSUPPORTED_KEYS))
            ->filter(fn(array $array): bool => array_filter(self::CURRENTLY_SUPPORTED_MODULES, fn(string $supportedModule): bool => str_starts_with($array[0], $supportedModule)) !== [])
            ->toArray();
    }

    #[DataProvider('getAllActionControllerDataProvider')]
    public function testHandle(string $key): void
    {
        $dic = $this->getContainer();

        self::$testSession->setUserById($this->getUserId($key));
        $vars = $this->getSpecificRequestVariables($key) + $this->getGeneralRequestVariables();
        request::setMockVars($vars);

        $game = $dic->get(GameControllerInterface::class);
        $subject = Init::getContainer()
            ->getDefinedImplementationsOf(ActionControllerInterface::class, true)->get($key);

        $subject->handle($game);
    }

    private function getUserId(string $key): int
    {
        return match ($key) {
            'SHIP_ACTIONS-B_PAY_TRADELICENSE' => 11,
            'TRADE_ACTIONS-B_TAKE_OFFER' => 102,
            default => 101
        };
    }

    private function getSpecificRequestVariables(string $key): array
    {
        return match ($key) {
            'SHIP_ACTIONS-B_FLEET_UP',
            'SHIP_ACTIONS-B_FLEET_ALERT_GREEN',
            'SHIP_ACTIONS-B_FLEET_ALERT_YELLOW',
            'SHIP_ACTIONS-B_FLEET_ALERT_RED' => ['id' => 77],
            'SHIP_ACTIONS-B_HIDE_FLEET',
            'SHIP_ACTIONS-B_SHOW_FLEET',
            'SHIP_ACTIONS-B_TOGGLE_FLEET' => ['fleet' => 77],
            'SHIP_ACTIONS-B_TRANSWARP' => ['transwarplayer' => 2],
            'SHIP_ACTIONS-B_COLONIZE' => ['fieldid' => 1],
            'SHIP_ACTIONS-B_CREATE_WEB' => ['id' => 1023],
            'SHIP_ACTIONS-B_CANCEL_WEB' => ['id' => 1024],
            'SHIP_ACTIONS-B_UNSUPPORT_WEB' => ['id' => 1025],
            'SHIP_ACTIONS-B_REMOVE_WEB',
            'SHIP_ACTIONS-B_IMPLODE_WEB',
            'SHIP_ACTIONS-B_SUPPORT_WEB' => ['id' => 1026],
            'SHIP_ACTIONS-B_GATHER_RESOURCES' => ['id' => 81, 'chosen' => 1488763],
            'SHIP_ACTIONS-B_LAND_SHUTTLE' => ['id' => 43],
            'SHIP_ACTIONS-B_PAY_TRADELICENSE' => ['id' => 10203, 'target' => 10203, 'method' => 'ship'],
            'SPACECRAFT_ACTIONS-B_ACTIVATE_SYSTEM',
            'SPACECRAFT_ACTIONS-B_DEACTIVATE_SYSTEM' => ['type' => SpacecraftSystemTypeEnum::NBS->name],
            'SPACECRAFT_ACTIONS-B_START_SHUTTLE' => ['shid' => 20061],
            'SPACECRAFT_ACTIONS-B_DUMP_CREWMAN',
            'SPACECRAFT_ACTIONS-B_RENAME_CREW' => ['crewid' => 2],
            'SPACECRAFT_ACTIONS-B_LOAD_REACTOR' => ['id' => 43, 'reactorload' => 5],
            'SPACECRAFT_ACTIONS-B_MOVE' => ['posx' => 7, 'posy' => 7],
            'SPACECRAFT_ACTIONS-B_SEND_BROADCAST' => ['text' => 'BROADCAST TXT'],
            'SPACECRAFT_ACTIONS-B_ADD_SHIP_LOG' => ['log' => 'LOGBOOK TXT'],
            'SPACECRAFT_ACTIONS-B_ATTACK_BUILDING' => ['id' => 77, 'field' => 25],
            'SPACECRAFT_ACTIONS-B_ADVENT_DOOR' => ['target' => 1],
            'SPACECRAFT_ACTIONS-B_EASTER_EGG' => ['target' => 2],
            'SPACECRAFT_ACTIONS-B_SPLIT_REACTOR_OUTPUT' => ['fleet' => 0, 'autocarryover' => 1],
            'COLONY_ACTIONS-B_REMOVE_WASTE' => ['commodity' => [2 => 100, 4 => 100]],
            'COLONY_ACTIONS-B_CHANGE_TORPS' => ['torpid' => 0],
            'COLONY_ACTIONS-B_CHANGE_FREQUENCY' => ['frequency' => '123'],
            'COLONY_ACTIONS-B_DISASSEMBLE_SHIP' => ['ship_id' => 77],
            'COLONY_ACTIONS-B_TRAIN_CREW' => ['crewcount' => 1],
            'COLONY_ACTIONS-B_SET_POPULATIONLIMIT' => ['poplimit' => 20],
            'COLONY_ACTIONS-B_SWITCH_COLONYMENU' => ['menu' => ColonyMenuEnum::MENU_SOCIAL->value],
            'COLONY_ACTIONS-B_CREATE_SANDBOX' => ['name' => 'SANDBOX NAME'],
            'COLONY_ACTIONS-B_LOAD_SHIELDS' => ['load' => 200],
            'COLONY_ACTIONS-B_SCROLL_BUILDMENU' => ['menu' => BuildMenuEnum::BUILDMENU_ENERGY->value],
            'COLONY_ACTIONS-B_CREATE_MODULES' => ['moduleids' => [], 'values' => [], 'func' => BuildingFunctionEnum::MODULEFAB_TYPE3_LVL1->value],
            'COLONY_ACTIONS-B_CANCEL_REPAIR' => ['shipid' => 78],
            'COLONY_ACTIONS-B_CANCEL_MODULECREATION' => ['module' => 1, 'func' => BuildingFunctionEnum::MODULEFAB_TYPE3_LVL1->value],
            'TRADE_ACTIONS-B_CREATE_OFFER' => ['storid' => 13, 'ggid' => 8, 'wgid' => 21, 'gcount' => 1, 'wcount' => 5, 'amount' => 1],
            'TRADE_ACTIONS-B_TAKE_OFFER',
            'TRADE_ACTIONS-B_CANCEL_OFFER' => ['offerid' => 1],
            'TRADE_ACTIONS-B_DEALS_TAKE_OFFER' => ['dealid' => 1],
            'TRADE_ACTIONS-B_DEALS_BID_AUCTION' => ['dealid' => 1],
            'TRADE_ACTIONS-B_DEALS_TAKE_AUCTION' => ['dealid' => 1],
            'TRADE_ACTIONS-B_BUY_LOTTERY_TICKETS' => ['amount' => 1],
            'TRADE_ACTIONS-B_TRADE_SEARCH_BOTH',
            'TRADE_ACTIONS-B_TRADE_SEARCH_DEMAND',
            'TRADE_ACTIONS-B_TRADE_SEARCH_OFFER' => ['cid' => 8],
            'TRADE_ACTIONS-B_TRANSFER' => ['storid' => 13],
            'TRADE_ACTIONS-B_BASIC_BUY',
            'TRADE_ACTIONS-B_BASIC_SELL' => ['uid' => 'uniq_123456'],
            default => []
        };
    }

    private function getGeneralRequestVariables(): array
    {
        return [
            'id' => 42,
            'target' => 43,
            'userid' => 101,
            'colonyid' => 42,
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
            'TOKEN' => 'MY_TOKEN',
            'is_unload' => 1,
            'transfer_type' => TransferTypeEnum::COMMODITIES->value,
            'source_type' => 'ship',
            'target_type' => 'station',

            // SHIP
            'rumpid' => 6501,
            'planid' => 2324,
            'shipid' => 42,
            'fleetid' => 77,
            'buoyid' => 42,
            'shuttle' => 100001,

            // COLONY
            'fid' => 26,
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
