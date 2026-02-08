<?php

declare(strict_types=1);

namespace Stu\Action;

use PHPUnit\Framework\Attributes\DataProvider;
use request;
use Stu\ActionTestCase;
use Stu\Component\Station\Dock\DockModeEnum;
use Stu\Component\Station\Dock\DockTypeEnum;
use Stu\Config\Init;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;

class AllStationActionsTest extends ActionTestCase
{
    public static function getStationActionControllerDataProvider(): array
    {
        $definedImplementations =  Init::getContainer()
            ->getDefinedImplementationsOf(ActionControllerInterface::class, true);

        return $definedImplementations
            ->map(fn (ActionControllerInterface $actionController): array => [$definedImplementations->indexOf($actionController)])
            ->filter(fn (array $array): bool => str_starts_with($array[0], 'STATION_ACTIONS'))
            ->toArray();
    }

    #[DataProvider('getStationActionControllerDataProvider')]
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
            default => 101
        };
    }

    private function getSpecificRequestVariables(string $key): array
    {
        return match ($key) {
            'STATION_ACTIONS-B_ADD_DOCKPRIVILEGE' => [
                'type' => DockTypeEnum::USER->value,
                'mode' => DockModeEnum::ALLOW->value,
                'target' => 101,
            ],
            'STATION_ACTIONS-B_REPAIR_SHIP' => ['ship_id' => 42],
            'STATION_ACTIONS-B_CANCEL_REPAIR' => ['shipid' => 10203],
            'STATION_ACTIONS-B_DOCK_FLEET' => ['fid' => 42],
            'STATION_ACTIONS-B_BUILD_SHIPYARD_SHIP' => ['planid' => 2324],
            'STATION_ACTIONS-B_DELETE_DOCKPRIVILEGE' => ['privilegeid' =>  1],
            default => []
        };
    }

    private function getGeneralRequestVariables(): array
    {
        return [
            'id' => 43,
            'target' => 10203
        ];
    }
}
