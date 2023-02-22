<?php

declare(strict_types=1);

namespace Stu\Module\Station\Action\AddDockPrivilege;

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
    public const ACTION_IDENTIFIER = 'B_ADD_DOCKPRIVILEGE';

    private ShipLoaderInterface $shipLoader;

    private FactionRepositoryInterface $factionRepository;

    private DockingPrivilegeRepositoryInterface $dockingPrivilegeRepository;

    private AllianceRepositoryInterface $allianceRepository;

    private UserRepositoryInterface $userRepository;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        FactionRepositoryInterface $factionRepository,
        DockingPrivilegeRepositoryInterface $dockingPrivilegeRepository,
        AllianceRepositoryInterface $allianceRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->shipLoader = $shipLoader;
        $this->factionRepository = $factionRepository;
        $this->dockingPrivilegeRepository = $dockingPrivilegeRepository;
        $this->allianceRepository = $allianceRepository;
        $this->userRepository = $userRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $target = (int) request::getIntFatal('target');
        $type = (int) request::getIntFatal('type');
        $mode = (int) request::getIntFatal('mode');

        $game->setView(ShowDockingPrivileges::VIEW_IDENTIFIER);
        if ($mode != ShipEnum::DOCK_PRIVILEGE_MODE_ALLOW && $mode != ShipEnum::DOCK_PRIVILEGE_MODE_DENY) {
            return;
        }
        if ($this->dockingPrivilegeRepository->existsForTargetAndTypeAndShip($target, $type, (int) $ship->getId()) === true) {
            return;
        }
        $save = 0;
        switch ($type) {
            case ShipEnum::DOCK_PRIVILEGE_USER:
                if ($this->userRepository->find((int) $target) === null) {
                    break;
                }
                $save = 1;
                break;
            case ShipEnum::DOCK_PRIVILEGE_ALLIANCE:
                if ($this->allianceRepository->find((int) $target) === null) {
                    break;
                }
                $save = 1;
                break;
            case ShipEnum::DOCK_PRIVILEGE_FACTION:
                if ($this->factionRepository->find((int) $target) === null) {
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

    public function performSessionCheck(): bool
    {
        return true;
    }
}
