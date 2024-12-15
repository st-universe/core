<?php

declare(strict_types=1);

namespace Stu\Module\Station\Action\AddDockPrivilege;

use Override;
use request;
use Stu\Component\Station\Dock\DockModeEnum;
use Stu\Component\Station\Dock\DockTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Station\Lib\StationLoaderInterface;
use Stu\Module\Station\View\ShowDockingPrivileges\ShowDockingPrivileges;
use Stu\Orm\Repository\AllianceRepositoryInterface;
use Stu\Orm\Repository\DockingPrivilegeRepositoryInterface;
use Stu\Orm\Repository\FactionRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class AddDockPrivilege implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_ADD_DOCKPRIVILEGE';

    public function __construct(
        private StationLoaderInterface $stationLoader,
        private FactionRepositoryInterface $factionRepository,
        private DockingPrivilegeRepositoryInterface $dockingPrivilegeRepository,
        private AllianceRepositoryInterface $allianceRepository,
        private UserRepositoryInterface $userRepository,
        private ShipRepositoryInterface $shipRepository
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $station = $this->stationLoader->getByIdAndUser(
            request::indInt('id'),
            $userId,
            false,
            false
        );

        $target = request::getIntFatal('target');
        $type = DockTypeEnum::from(request::getIntFatal('type'));
        $mode = DockModeEnum::from(request::getIntFatal('mode'));

        $game->setView(ShowDockingPrivileges::VIEW_IDENTIFIER);
        if ($this->dockingPrivilegeRepository->existsForTargetAndTypeAndShip($target, $type, $station->getId()) === true) {
            return;
        }
        $save = 0;
        switch ($type) {
            case DockTypeEnum::USER:
                if ($this->userRepository->find($target) === null) {
                    break;
                }
                $save = 1;
                break;
            case DockTypeEnum::ALLIANCE:
                if ($this->allianceRepository->find($target) === null) {
                    break;
                }
                $save = 1;
                break;
            case DockTypeEnum::FACTION:
                if ($this->factionRepository->find($target) === null) {
                    break;
                }
                $save = 1;
                break;
            case DockTypeEnum::SHIP:
                if ($this->shipRepository->find($target) === null) {
                    break;
                }
                $save = 1;
                break;
        }
        if ($save == 1) {
            $dock = $this->dockingPrivilegeRepository->prototype();
            $dock->setPrivilegeMode($mode);
            $dock->setPrivilegeType($type);
            $dock->setTargetId($target);
            $dock->setStation($station);

            $this->dockingPrivilegeRepository->save($dock);
        }
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
