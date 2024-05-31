<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\EpsTransfer;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\Interaction\InteractionChecker;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class EpsTransfer implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_ETRANSFER';

    private ShipLoaderInterface $shipLoader;

    private PrivateMessageSenderInterface $privateMessageSender;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        PrivateMessageSenderInterface $privateMessageSender
    ) {
        $this->shipLoader = $shipLoader;
        $this->privateMessageSender = $privateMessageSender;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $shipId = request::indInt('id');
        $targetId = request::postIntFatal('target');

        $wrappers = $this->shipLoader->getWrappersBySourceAndUserAndTarget(
            $shipId,
            $userId,
            $targetId
        );

        $wrapper = $wrappers->getSource();
        $targetWrapper = $wrappers->getTarget();
        if ($targetWrapper === null) {
            return;
        }
        $target = $targetWrapper->get();

        $ship = $wrapper->get();
        if (!$ship->hasEnoughCrew($game)) {
            return;
        }
        if (!InteractionChecker::canInteractWith($ship, $target, $game, true)) {
            return;
        }

        $eps = $wrapper->getEpsSystemData();

        if ($eps === null || $eps->getEps() == 0) {
            $game->addInformation(_("Keine Energie vorhanden"));
            return;
        }
        if ($ship->getCloakState()) {
            $game->addInformation(_("Die Tarnung ist aktiviert"));
            return;
        }
        if ($ship->isWarped()) {
            $game->addInformation("Schiff befindet sich im Warp");
            return;
        }

        if ($target->isDestroyed()) {
            return;
        }
        if ($target->isWarped()) {
            $game->addInformation(sprintf(_('Die %s befindet sich im Warp'), $target->getName()));
            return;
        }
        $load = request::postInt('ecount');
        if ($load < 1) {
            $game->addInformation(_("Es wurde keine Energiemenge angegeben"));
            return;
        }

        $targetEps = $targetWrapper->getEpsSystemData();

        if ($targetEps === null) {
            $game->addInformation(sprintf(_('Die %s hat kein Energiesystem installiert'), $target->getName()));
            return;
        }
        if ($targetEps->getBattery() >= $targetEps->getMaxBattery()) {
            $game->addInformation(sprintf(_('Die Ersatzbatterie der %s ist bereits voll'), $target->getName()));
            return;
        }
        if ($load * 3 > $eps->getEps()) {
            $load = (int) floor($eps->getEps() / 3);
        }
        if ($load + $targetEps->getBattery() > $targetEps->getMaxBattery()) {
            $load = $targetEps->getMaxBattery() - $targetEps->getBattery();
        }
        $eps->lowerEps($load * 3)->update();
        $targetEps->setBattery($targetEps->getBattery() + $load)->update();

        $this->privateMessageSender->send(
            $userId,
            $target->getUser()->getId(),
            "Die " . $ship->getName() . " transferiert in Sektor " . $ship->getSectorString() . " " . $load . " Energie in die Batterie der " . $target->getName(),
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_TRADE
        );
        $game->addInformation(sprintf(_('Es wurde %d Energie zur %s transferiert'), $load, $target->getName()));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
