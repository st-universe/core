<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\DockShip;

use request;
use Stu\Component\Ship\ShipEnum;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Ship\Lib\PositionCheckerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\DockingPrivilegeRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Component\Ship\System\Exception\ShipSystemException;

final class DockShip implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DOCK';

    private ShipLoaderInterface $shipLoader;

    private DockingPrivilegeRepositoryInterface $dockingPrivilegeRepository;

    private PrivateMessageSenderInterface $privateMessageSender;

    private ShipRepositoryInterface $shipRepository;

    private ShipSystemManagerInterface $shipSystemManager;

    private PositionCheckerInterface $positionChecker;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        DockingPrivilegeRepositoryInterface $dockingPrivilegeRepository,
        PrivateMessageSenderInterface $privateMessageSender,
        ShipRepositoryInterface $shipRepository,
        ShipSystemManagerInterface $shipSystemManager,
        PositionCheckerInterface $positionChecker
    ) {
        $this->shipLoader = $shipLoader;
        $this->dockingPrivilegeRepository = $dockingPrivilegeRepository;
        $this->privateMessageSender = $privateMessageSender;
        $this->shipRepository = $shipRepository;
        $this->shipSystemManager = $shipSystemManager;
        $this->positionChecker = $positionChecker;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );
        $target = $this->shipRepository->find(request::indInt('target'));
        if ($target === null) {
            return;
        }
        if (!$this->positionChecker->checkPosition($target, $ship)) {
            return;
        }
        if ($ship->getDockedTo()) {
            return;
        }
        if (!$target->isBase()) {
            return;
        }

        if (!$ship->hasEnoughCrew()) {
            $game->addInformationf(
                _("Es werden %d Crewmitglieder benötigt"),
                $ship->getBuildplan()->getCrew()
            );
            return;
        }

        if (!$this->checkPrivilegeFor((int) $target->getId(), $game->getUser())) {

            $href = sprintf(_('ship.php?SHOW_SHIP=1&id=%d'), $target->getId());

            $this->privateMessageSender->send(
                $userId,
                $target->getUser()->getId(),
                sprintf(
                    'Der Andockversuch der %s von Spieler %s bei %s wurde verweigert',
                    $ship->getName(),
                    $ship->getUser()->getName(),
                    $target->getName()
                ),
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_STATION,
                $href
            );

            $game->addInformation('Das Andocken wurde verweigert');
            return;
        }
        if ($ship->isFleetLeader()) {
            $this->fleetDock($ship, $target, $game);
            //TODO PM raus schicken!
            return;
        }

        if ($ship->getEps() < ShipSystemTypeEnum::SYSTEM_ECOST_DOCK) {
            $game->addInformation('Zum Andocken wird 1 Energie benötigt');
            return;
        }
        if (!$target->hasFreeDockingSlots()) {
            $game->addInformation('Zur Zeit sind alle Dockplätze belegt');
            return;
        }
        if ($ship->getCloakState()) {
            $game->addInformation("Das Schiff ist getarnt");
            return;
        }

        try {
            $this->shipSystemManager->deactivate($ship, ShipSystemTypeEnum::SYSTEM_SHIELDS);
        } catch (ShipSystemException $e) {
        }

        $ship->cancelRepair();
        $ship->setEps($ship->getEps() - 1);
        $ship->setDockedTo($target);

        $this->shipRepository->save($ship);

        $this->privateMessageSender->send(
            $userId,
            (int)$target->getUser()->getId(),
            'Die ' . $ship->getName() . ' hat an der ' . $target->getName() . ' angedockt',
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_STATION
        );
        $game->addInformation('Andockvorgang abgeschlossen');
    }

    private function fleetDock(ShipInterface $ship, ShipInterface $target, GameControllerInterface $game): void
    {
        $msg = [];
        $msg[] = _("Flottenbefehl ausgeführt: Andocken an ") . $target->getName();;
        $freeSlots = $target->getFreeDockingSlotCount();
        foreach ($ship->getFleet()->getShips() as $ship) {
            if ($freeSlots <= 0) {
                $msg[] = _("Es sind alle Dockplätze belegt");
                break;
            }
            if ($ship->getDockedTo()) {
                continue;
            }
            if ($ship->getEps() < ShipSystemTypeEnum::SYSTEM_ECOST_DOCK) {
                $msg[] = $ship->getName() . _(": Nicht genügend Energie vorhanden");
                continue;
            }
            if ($ship->getCloakState()) {
                $msg[] = $ship->getName() . _(': Das Schiff ist getarnt');
                continue;
            }
            $ship->cancelRepair();

            try {
                $this->shipSystemManager->deactivate($ship, ShipSystemTypeEnum::SYSTEM_SHIELDS);
            } catch (ShipSystemException $e) {
            }

            try {
                $this->shipSystemManager->deactivate($ship, ShipSystemTypeEnum::SYSTEM_WARPDRIVE);
            } catch (ShipSystemException $e) {
            }

            $ship->setDockedTo($target);

            $ship->setEps($ship->getEps() - ShipSystemTypeEnum::SYSTEM_ECOST_DOCK);

            $this->shipRepository->save($ship);

            $freeSlots--;
        }
        $game->addInformationMerge($msg);
    }

    private function checkPrivilegeFor(int $shipId, UserInterface $user): bool
    {
        $privileges = $this->dockingPrivilegeRepository->getByShip($shipId);
        if ($privileges === []) {
            return false;
        }
        $allowed = false;
        foreach ($privileges as $key => $priv) {
            switch ($priv->getPrivilegeType()) {
                case ShipEnum::DOCK_PRIVILEGE_USER:
                    if ($priv->getTargetId() == $user->getId()) {
                        if ($priv->getPrivilegeMode() == ShipEnum::DOCK_PRIVILEGE_MODE_DENY) {
                            return false;
                        }
                        $allowed = true;
                    }
                    break;
                case ShipEnum::DOCK_PRIVILEGE_ALLIANCE:
                    if ($priv->getTargetId() == $user->getAllianceId()) {
                        if ($priv->getPrivilegeMode() == ShipEnum::DOCK_PRIVILEGE_MODE_DENY) {
                            return false;
                        }
                        $allowed = true;
                    }
                    break;
                case ShipEnum::DOCK_PRIVILEGE_FACTION:
                    if ($priv->getTargetId() == $user->getFactionId()) {
                        if ($priv->getPrivilegeMode() == ShipEnum::DOCK_PRIVILEGE_MODE_DENY) {
                            return false;
                        }
                        $allowed = true;
                    }
                    break;
            }
        }
        return $allowed;
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
