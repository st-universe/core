<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\EpsTransfer;

use ObjectNotFoundException;
use PM;
use request;
use ShipData;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class EpsTransfer implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_ETRANSFER';

    private $shipLoader;

    public function __construct(
        ShipLoaderInterface $shipLoader
    ) {
        $this->shipLoader = $shipLoader;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );
        if ($ship->getBuildplan()->getCrew() > 0 && $ship->getCrew() == 0) {
            $game->addInformationf(
                _("Es werden %d Crewmitglieder benötigt"),
                $ship->getBuildplan()->getCrew()
            );
            return;
        }

        if ($ship->getEps() == 0) {
            $game->addInformation(_("Keine Energie vorhanden"));
            return;
        }
        if ($ship->getCloakState()) {
            $game->addInformation(_("Die Tarnung ist aktiviert"));
            return;
        }
        if ($ship->getWarpState()) {
            $game->addInformation(_("Der Warpantrieb ist aktiviert"));
            return;
        }
        $target = $this->shipLoader->getById(request::postIntFatal('target'));
        if (!$this->preChecks($ship, $target, $game)) {
            return;
        }
        if ($target->getWarpState()) {
            $game->addInformation(sprintf(_('Die %s befindet sich im Warp'), $target->getName()));
            return;
        }
        $load = request::postInt('ecount');
        if ($load < 1) {
            $game->addInformation(_("Es wurde keine Energiemenge angegeben"));
            return;
        }
        if ($target->getEBatt() >= $target->getMaxEBatt()) {
            $game->addInformation(sprintf(_('Der Energiespeicher der %s ist voll'), $target->getName()));
            return;
        }
        if ($load * 3 > $ship->getEps()) {
            $load = floor($ship->getEps() / 3);
        }
        if ($load + $target->getEbatt() > $target->getMaxEbatt()) {
            $load = $target->getMaxEbatt() - $target->getEbatt();
        }
        $ship->lowerEps($load * 3);
        $target->upperEbatt($load);
        $target->save();
        $ship->save();
        PM::sendPM(currentUser()->getId(), $target->getUserId(),
            "Die " . $ship->getName() . " transferiert in SeKtor " . $ship->getSectorString() . " " . $load . " Energie in die Batterie der " . $target->getName(),
            PM_SPECIAL_TRADE);
        $game->addInformation(sprintf(_('Es wurde %d Energie zur %s transferiert'), $load, $target->getName()));
    }

    private function preChecks(ShipData $ship, ShipData $target, GameControllerInterface $game): bool {
        if (!checkPosition($ship, $target) || $ship->getCloakState()) {
            new ObjectNotFoundException($target->getId());
        }
        if ($target->shieldIsActive() && $target->getUserId() != currentUser()->getId()) {
            $game->addInformation("Die " . $target->getName() . " hat die Schilde aktiviert");
            return false;
        }
        return true;
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
