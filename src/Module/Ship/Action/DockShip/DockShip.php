<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\DockShip;

use request;
use Stu\Component\Ship\Repair\CancelRepairInterface;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Ship\Lib\InteractionCheckerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ShipInterface;
use Stu\Component\Ship\System\Exception\ShipSystemException;
use Stu\Module\Ship\Lib\DockPrivilegeUtilityInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

final class DockShip implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DOCK';

    private ShipLoaderInterface $shipLoader;

    private DockPrivilegeUtilityInterface $dockPrivilegeUtility;

    private PrivateMessageSenderInterface $privateMessageSender;

    private ShipSystemManagerInterface $shipSystemManager;

    private InteractionCheckerInterface $interactionChecker;

    private CancelRepairInterface $cancelRepair;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        DockPrivilegeUtilityInterface $dockPrivilegeUtility,
        PrivateMessageSenderInterface $privateMessageSender,
        ShipSystemManagerInterface $shipSystemManager,
        InteractionCheckerInterface $interactionChecker,
        CancelRepairInterface $cancelRepair
    ) {
        $this->shipLoader = $shipLoader;
        $this->dockPrivilegeUtility = $dockPrivilegeUtility;
        $this->privateMessageSender = $privateMessageSender;
        $this->shipSystemManager = $shipSystemManager;
        $this->interactionChecker = $interactionChecker;
        $this->cancelRepair = $cancelRepair;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $shipId = request::indInt('id');
        $targetId = request::indInt('target');

        $shipArray = $this->shipLoader->getWrappersByIdAndUserAndTarget(
            $shipId,
            $userId,
            $targetId
        );

        $wrapper = $shipArray[$shipId];
        $ship = $wrapper->get();

        $targetWrapper = $shipArray[$targetId];
        if ($targetWrapper === null) {
            return;
        }
        $target = $targetWrapper->get();

        if (!$this->interactionChecker->checkPosition($target, $ship)) {
            return;
        }
        if ($ship->getDockedTo()) {
            return;
        }
        if (!$target->isBase()) {
            return;
        }

        if (!$ship->hasEnoughCrew($game)) {
            return;
        }

        if ($ship->isTractored()) {
            $game->addInformation(_('Das Schiff wird von einem Traktorstrahl gehalten'));
            return;
        }

        if ($target->getShieldState()) {
            $game->addInformation(_("Aktion nicht möglich. Die Station hat die Schilde aktiviert"));
            return;
        }

        if (!$this->dockPrivilegeUtility->checkPrivilegeFor((int) $target->getId(), $game->getUser())) {

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
            $this->fleetDock($wrapper, $target, $game);
            return;
        }

        $epsSystem = $wrapper->getEpsSystemData();
        if ($epsSystem->getEps() < ShipSystemTypeEnum::SYSTEM_ECOST_DOCK) {
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
            $this->shipSystemManager->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_SHIELDS);
        } catch (ShipSystemException $e) {
        }

        if ($this->cancelRepair->cancelRepair($ship)) {
            $game->addInformation("Die Reparatur wurde abgebrochen");
        }
        $epsSystem->setEps($epsSystem->getEps() - 1)->update();
        $ship->setDockedTo($target);

        $this->shipLoader->save($ship);

        $href = sprintf(_('ship.php?SHOW_SHIP=1&id=%d'), $target->getId());

        $this->privateMessageSender->send(
            $userId,
            (int)$target->getUser()->getId(),
            'Die ' . $ship->getName() . ' hat an der ' . $target->getName() . ' angedockt',
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_STATION,
            $href
        );
        $game->addInformation('Andockvorgang abgeschlossen');
    }

    private function fleetDock(ShipWrapperInterface $wrapper, ShipInterface $target, GameControllerInterface $game): void
    {
        $ship = $wrapper->get();

        $msg = [];
        $msg[] = _("Flottenbefehl ausgeführt: Andocken an ") . $target->getName();;
        $freeSlots = $target->getFreeDockingSlotCount();
        foreach ($wrapper->getFleetWrapper()->getShipWrappers() as $fleetShipWrapper) {

            if ($freeSlots <= 0) {
                $msg[] = _("Es sind alle Dockplätze belegt");
                break;
            }

            $fleetShip = $fleetShipWrapper->get();
            if ($fleetShip->getDockedTo()) {
                continue;
            }

            $epsSystem = $fleetShipWrapper->getEpsSystemData();
            if ($epsSystem->getEps() < ShipSystemTypeEnum::SYSTEM_ECOST_DOCK) {
                $msg[] = $fleetShip->getName() . _(": Nicht genügend Energie vorhanden");
                continue;
            }

            if ($fleetShip->getCloakState()) {
                $msg[] = $fleetShip->getName() . _(': Das Schiff ist getarnt');
                continue;
            }
            if ($this->cancelRepair->cancelRepair($fleetShip)) {
                $msg[] = $fleetShip->getName() . _(': Die Reparatur wurde abgebrochen');
                continue;
            }

            try {
                $this->shipSystemManager->deactivate($fleetShipWrapper, ShipSystemTypeEnum::SYSTEM_SHIELDS);
            } catch (ShipSystemException $e) {
            }

            try {
                $this->shipSystemManager->deactivate($fleetShipWrapper, ShipSystemTypeEnum::SYSTEM_WARPDRIVE);
            } catch (ShipSystemException $e) {
            }

            $fleetShip->setDockedTo($target);

            $epsSystem->setEps($epsSystem->getEps() - ShipSystemTypeEnum::SYSTEM_ECOST_DOCK)->update();

            $this->shipLoader->save($fleetShip);

            $freeSlots--;
        }

        $href = sprintf(_('ship.php?SHOW_SHIP=1&id=%d'), $target->getId());

        $this->privateMessageSender->send(
            $game->getUser()->getId(),
            $target->getUser()->getId(),
            'Die Flotte ' . $ship->getFleet()->getName() . ' hat an der ' . $target->getName() . ' angedockt',
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_STATION,
            $href
        );
        $game->addInformationMerge($msg);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
