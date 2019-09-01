<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\AddDockPrivilege;

use Alliance;
use DockingRights;
use DockingRightsData;
use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowDockingPrivileges\ShowDockingPrivileges;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\FactionRepositoryInterface;
use User;

final class AddDockPrivilege implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_ADD_DOCKPRIVILEGE';

    private $shipLoader;

    private $factionRepository;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        FactionRepositoryInterface $factionRepository
    ) {
        $this->shipLoader = $shipLoader;
        $this->factionRepository = $factionRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $target = request::getIntFatal('target');
        $type = request::getIntFatal('type');
        $mode = request::getIntFatal('mode');

        $game->setView(ShowDockingPrivileges::VIEW_IDENTIFIER);
        if ($mode != DOCK_PRIVILEGE_MODE_ALLOW && $mode != DOCK_PRIVILEGE_MODE_DENY) {
            return;
        }
        if (count(DockingRights::getBy($ship->getId(), $target, $type)) != 0) {
            return;
        }
        $save = 0;
        switch ($type) {
            case DOCK_PRIVILEGE_USER:
                if (!User::getUserById($target)) {
                    break;
                }
                $save = 1;
                break;
            case DOCK_PRIVILEGE_ALLIANCE:
                if (!Alliance::getById($target)) {
                    break;
                }
                $save = 1;
                break;
            case DOCK_PRIVILEGE_FACTION:
                if ($this->factionRepository->find((int) $target) === null) {
                    break;
                }
                $save = 1;
                break;
            default:
                break;
        }
        if ($save == 1) {
            $dock = new DockingRightsData;
            $dock->setPrivilegeMode($mode);
            $dock->setPrivilegeType($type);
            $dock->setTargetId($target);
            $dock->setShipId($ship->getId());
            $dock->save();
        }
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
