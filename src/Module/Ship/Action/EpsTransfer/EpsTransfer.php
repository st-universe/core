<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\EpsTransfer;

use request;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\InteractionChecker;
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

        $shipArray = $this->shipLoader->getByIdAndUserAndTarget(
            $shipId,
            $userId,
            $targetId
        );

        $ship = $shipArray[$shipId];
        $target = $shipArray[$targetId];

        if (!$ship->hasEnoughCrew($game)) {
            return;
        }
        if (!InteractionChecker::canInteractWith($ship, $target, $game, false, true)) {
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
        if ($target === null) {
            return;
        }
        if ($target->getIsDestroyed()) {
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
            $game->addInformation(sprintf(_('Die Ersatzbatterie der %s ist bereits voll'), $target->getName()));
            return;
        }
        if ($load * 3 > $ship->getEps()) {
            $load = (int) floor($ship->getEps() / 3);
        }
        if ($load + $target->getEbatt() > $target->getMaxEbatt()) {
            $load = $target->getMaxEbatt() - $target->getEbatt();
        }
        $ship->setEps($ship->getEps() - $load * 3);
        $target->setEBatt($target->getEBatt() + $load);

        $this->shipLoader->save($target);
        $this->shipLoader->save($ship);

        $this->privateMessageSender->send(
            $userId,
            (int)$target->getUser()->getId(),
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
