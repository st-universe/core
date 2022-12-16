<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\TorpedoTransfer;

use request;

use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\InteractionChecker;
use Stu\Module\Ship\Lib\ShipTorpedoManagerInterface;

final class TorpedoTransfer implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_TORPEDO_TRANSFER';

    private ShipLoaderInterface $shipLoader;

    private ShipTorpedoManagerInterface $shipTorpedoManager;

    private PrivateMessageSenderInterface $privateMessageSender;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipTorpedoManagerInterface $shipTorpedoManager,
        PrivateMessageSenderInterface $privateMessageSender
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipTorpedoManager = $shipTorpedoManager;
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
        if ($target === null) {
            return;
        }
        if (!InteractionChecker::canInteractWith($ship, $target, $game, false, true)) {
            return;
        }

        if (!$ship->isSystemHealthy(ShipSystemTypeEnum::SYSTEM_TORPEDO_STORAGE)) {
            $game->addInformation(_("Das Torpedolager ist zerstört"));
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
        if ($ship->getShieldState()) {
            $game->addInformation(_("Die Schilde sind aktiviert"));
            return;
        }

        $isUnload = request::has('isUnload');

        if ($target->getWarpState()) {
            $game->addInformation(sprintf(_('Die %s befindet sich im Warp'), $target->getName()));
            return;
        }
        if ($ship->getTorpedoCount() > 0 && $target->getTorpedoCount() > 0 && $ship->getTorpedo() !== $target->getTorpedo()) {
            $game->addInformation(_("Die Schiffe haben unterschiedliche Torpedos geladen"));
            return;
        }

        $requestedTransferCount = request::postInt('tcount');

        if ($isUnload) {
            $amount = min(
                $requestedTransferCount,
                $ship->getTorpedoCount(),
                $target->getMaxTorpedos() - $target->getTorpedoCount()
            );

            if ($amount > 0) {
                if ($target->getRump()->getTorpedoLevel() !== $ship->getTorpedo()->getLevel()) {
                    $game->addInformation(sprintf(_('Die %s kann den Torpedotyp nicht ausrüsten'), $target->getName()));
                    return;
                }

                $this->shipTorpedoManager->changeTorpedo($target, $amount, $ship->getTorpedo());
                $this->shipTorpedoManager->changeTorpedo($ship, -$amount);
            }
        } else {
            $amount = min(
                $requestedTransferCount,
                $target->getTorpedoCount(),
                $ship->getMaxTorpedos() - $ship->getTorpedoCount()
            );

            if ($amount > 0) {
                $this->shipTorpedoManager->changeTorpedo($ship, $amount, $target->getTorpedo());
                $this->shipTorpedoManager->changeTorpedo($target, -$amount);
            }
        }

        $game->addInformation(
            sprintf(
                _('Die %s hat %d Torpedos %s der %s transferiert'),
                $ship->getName(),
                $amount,
                $isUnload ? 'zu' : 'von',
                $target->getName()
            )
        );

        $this->privateMessageSender->send(
            $userId,
            $target->getUser()->getId(),
            sprintf(
                'Die %s hat in Sektor %s %d Torpedos %s %s transferiert',
                $ship->getName(),
                $ship->getSectorString(),
                $amount,
                $isUnload ? 'zur' : 'von der',
                $target->getName()
            ),
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_TRADE
        );
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
