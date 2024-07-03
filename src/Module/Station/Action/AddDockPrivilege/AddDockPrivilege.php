<?php

declare(strict_types=1);

namespace Stu\Module\Station\Action\AddDockPrivilege;

use Override;
use request;
use Stu\Component\Ship\ShipEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Station\View\ShowDockingPrivileges\ShowDockingPrivileges;
use Stu\Orm\Repository\AllianceRepositoryInterface;
use Stu\Orm\Repository\DockingPrivilegeRepositoryInterface;
use Stu\Orm\Repository\FactionRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class AddDockPrivilege implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_ADD_DOCKPRIVILEGE';

    public function __construct(private ShipLoaderInterface $shipLoader, private FactionRepositoryInterface $factionRepository, private DockingPrivilegeRepositoryInterface $dockingPrivilegeRepository, private AllianceRepositoryInterface $allianceRepository, private UserRepositoryInterface $userRepository)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId,
            false,
            false
        );

        $target = request::getIntFatal('target');
        $type = request::getIntFatal('type');
        $mode = request::getIntFatal('mode');

        $game->setView(ShowDockingPrivileges::VIEW_IDENTIFIER);
        if ($mode != ShipEnum::DOCK_PRIVILEGE_MODE_ALLOW && $mode != ShipEnum::DOCK_PRIVILEGE_MODE_DENY) {
            return;
        }
        if ($this->dockingPrivilegeRepository->existsForTargetAndTypeAndShip($target, $type, $ship->getId()) === true) {
            return;
        }
        $save = 0;
        switch ($type) {
            case ShipEnum::DOCK_PRIVILEGE_USER:
                if ($this->userRepository->find($target) === null) {
                    break;
                }
                $save = 1;
                break;
            case ShipEnum::DOCK_PRIVILEGE_ALLIANCE:
                if ($this->allianceRepository->find($target) === null) {
                    break;
                }
                $save = 1;
                break;
            case ShipEnum::DOCK_PRIVILEGE_FACTION:
                if ($this->factionRepository->find($target) === null) {
                    break;
                }
                $save = 1;
                break;
            default:
                break;
        }
        if ($save == 1) {
            $dock = $this->dockingPrivilegeRepository->prototype();
            $dock->setPrivilegeMode($mode);
            $dock->setPrivilegeType($type);
            $dock->setTargetId($target);
            $dock->setShip($ship);

            $this->dockingPrivilegeRepository->save($dock);
        }
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
