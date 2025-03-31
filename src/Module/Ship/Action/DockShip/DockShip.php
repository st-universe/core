<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\DockShip;

use Override;
use request;
use Stu\Component\Spacecraft\Repair\CancelRepairInterface;
use Stu\Component\Ship\Retrofit\CancelRetrofitInterface;
use Stu\Component\Ship\ShipEnum;
use Stu\Component\Spacecraft\System\Exception\SpacecraftSystemException;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Station\Dock\DockPrivilegeUtilityInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Spacecraft\Lib\Interaction\InteractionCheckerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Entity\StationInterface;

final class DockShip implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DOCK';

    public function __construct(
        private ShipLoaderInterface $shipLoader,
        private DockPrivilegeUtilityInterface $dockPrivilegeUtility,
        private PrivateMessageSenderInterface $privateMessageSender,
        private SpacecraftSystemManagerInterface $spacecraftSystemManager,
        private InteractionCheckerInterface $interactionChecker,
        private CancelRepairInterface $cancelRepair,
        private CancelRetrofitInterface $cancelRetrofit
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $shipId = request::indInt('id');
        $targetId = request::indInt('target');

        $wrappers = $this->shipLoader->getWrappersBySourceAndUserAndTarget(
            $shipId,
            $userId,
            $targetId
        );

        $wrapper = $wrappers->getSource();
        $ship = $wrapper->get();

        $targetWrapper = $wrappers->getTarget();
        if ($targetWrapper === null) {
            return;
        }
        $target = $targetWrapper->get();
        if (!$target instanceof StationInterface) {
            return;
        }

        if (!$this->interactionChecker->checkPosition($target, $ship)) {
            return;
        }
        if ($ship->getDockedTo() !== null) {
            return;
        }
        if (!$target->isStation()) {
            return;
        }

        if (!$ship->hasEnoughCrew($game)) {
            return;
        }

        if ($ship->isTractored()) {
            $game->addInformation(_('Das Schiff wird von einem Traktorstrahl gehalten'));
            return;
        }

        if ($target->isShielded()) {
            $game->addInformation(_("Aktion nicht möglich. Die Station hat die Schilde aktiviert"));
            return;
        }

        if (!$this->dockPrivilegeUtility->checkPrivilegeFor($target->getId(), $ship)) {

            $this->privateMessageSender->send(
                $userId,
                $target->getUser()->getId(),
                sprintf(
                    'Der Andockversuch der %s von Spieler %s bei %s wurde verweigert',
                    $ship->getName(),
                    $ship->getUser()->getName(),
                    $target->getName()
                ),
                PrivateMessageFolderTypeEnum::SPECIAL_STATION,
                $target
            );

            $game->addInformation('Das Andocken wurde verweigert');
            return;
        }

        $fleetWrapper = $wrapper->getFleetWrapper();
        if ($ship->isFleetLeader() && $fleetWrapper !== null) {
            $this->fleetDock($fleetWrapper, $target, $game);
            return;
        }

        $epsSystem = $wrapper->getEpsSystemData();
        if ($epsSystem === null || $epsSystem->getEps() < ShipEnum::SYSTEM_ECOST_DOCK) {
            $game->addInformation('Zum Andocken wird 1 Energie benötigt');
            return;
        }
        if (!$target->hasFreeDockingSlots()) {
            $game->addInformation('Zur Zeit sind alle Dockplätze belegt');
            return;
        }
        if ($ship->isCloaked()) {
            $game->addInformation("Das Schiff ist getarnt");
            return;
        }

        try {
            $this->spacecraftSystemManager->deactivate($wrapper, SpacecraftSystemTypeEnum::SHIELDS);
        } catch (SpacecraftSystemException) {
        }

        if ($this->cancelRepair->cancelRepair($ship)) {
            $game->addInformation("Die Reparatur wurde abgebrochen");
        }
        if ($this->cancelRetrofit->cancelRetrofit($ship)) {
            $game->addInformation("Die Umrüstung wurde abgebrochen");
        }
        $epsSystem->lowerEps(1)->update();
        $ship->setDockedTo($target);

        $this->shipLoader->save($ship);

        $this->privateMessageSender->send(
            $userId,
            $target->getUser()->getId(),
            'Die ' . $ship->getName() . ' hat an der ' . $target->getName() . ' angedockt',
            PrivateMessageFolderTypeEnum::SPECIAL_STATION,
            $target,
            $this->isAutoReadOnDock($target)
        );
        $game->addInformation('Andockvorgang abgeschlossen');
    }

    private function fleetDock(
        FleetWrapperInterface $fleetWrapper,
        StationInterface $target,
        GameControllerInterface $game
    ): void {
        $msg = [_("Flottenbefehl ausgeführt: Andocken an ") . $target->getName()];

        $freeSlots = $target->getFreeDockingSlotCount();
        foreach ($fleetWrapper->getShipWrappers() as $fleetShipWrapper) {
            if ($freeSlots <= 0) {
                $msg[] = _("Es sind alle Dockplätze belegt");
                break;
            }

            $fleetShip = $fleetShipWrapper->get();
            if ($fleetShip->getDockedTo() !== null) {
                continue;
            }

            if (!$fleetShip->hasEnoughCrew()) {
                $msg[] = sprintf(
                    _('%s: Nicht genügend Crew vorhanden'),
                    $fleetShip->getName()
                );
                continue;
            }

            $epsSystem = $fleetShipWrapper->getEpsSystemData();
            if ($epsSystem === null || $epsSystem->getEps() < ShipEnum::SYSTEM_ECOST_DOCK) {
                $msg[] = $fleetShip->getName() . _(": Nicht genügend Energie vorhanden");
                break;
            }

            if ($fleetShip->isCloaked()) {
                $msg[] = $fleetShip->getName() . _(': Das Schiff ist getarnt');
                continue;
            }
            if ($this->cancelRepair->cancelRepair($fleetShip)) {
                $msg[] = $fleetShip->getName() . _(': Die Reparatur wurde abgebrochen');
                continue;
            }
            if ($this->cancelRetrofit->cancelRetrofit($fleetShip)) {
                $msg[] = $fleetShip->getName() . _(': Die Umrüstung wurde abgebrochen');
                continue;
            }

            try {
                $this->spacecraftSystemManager->deactivate($fleetShipWrapper, SpacecraftSystemTypeEnum::SHIELDS);
            } catch (SpacecraftSystemException) {
            }

            try {
                $this->spacecraftSystemManager->deactivate($fleetShipWrapper, SpacecraftSystemTypeEnum::WARPDRIVE);
            } catch (SpacecraftSystemException) {
            }

            $fleetShip->setDockedTo($target);

            $epsSystem->lowerEps(ShipEnum::SYSTEM_ECOST_DOCK)->update();

            $this->shipLoader->save($fleetShip);

            $freeSlots--;
        }

        $this->privateMessageSender->send(
            $game->getUser()->getId(),
            $target->getUser()->getId(),
            'Die Flotte ' . $fleetWrapper->get()->getName() . ' hat an der ' . $target->getName() . ' angedockt',
            PrivateMessageFolderTypeEnum::SPECIAL_STATION,
            $target,
            $this->isAutoReadOnDock($target)
        );
        $game->addInformationMerge($msg);
    }

    private function isAutoReadOnDock(StationInterface $target): bool
    {
        $tradePost = $target->getTradePost();
        if ($tradePost === null) {
            return false;
        }

        return $tradePost->isDockPmAutoRead();
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
