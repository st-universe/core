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

final class TorpedoTransfer implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_TORPEDO_TRANSFER';

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

        if ($target === null) {
            return;
        }
        if (!$ship->canInteractWith($target, false, true)) {
            return;
        }
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

                $ship->setTorpedoCount($ship->getTorpedoCount() - $amount);
                $target->setTorpedoCount($target->getTorpedoCount() + $amount);
                $target->setTorpedo($ship->getTorpedo());
            }
        } else {
            $amount = min(
                $requestedTransferCount,
                $target->getTorpedoCount(),
                $ship->getMaxTorpedos() - $ship->getTorpedoCount()
            );

            if ($amount > 0) {
                $ship->setTorpedoCount($ship->getTorpedoCount() + $amount);
                $ship->setTorpedo($target->getTorpedo());
                $target->setTorpedoCount($target->getTorpedoCount() - $amount);
            }
        }

        if ($ship->getTorpedoCount() === 0) {
            $ship->setTorpedo(null);
        }
        if ($target->getTorpedoCount() === 0) {
            $target->setTorpedo(null);
        }

        $this->shipLoader->save($ship);
        $this->shipLoader->save($target);

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
            (int)$target->getUser()->getId(),
            "Die " . $ship->getName() . " hat in Sektor " . $ship->getSectorString() . " " . $amount . " Torpedos " . $isUnload ? "zur" : "von der" . $target->getName() . "transferiert",
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_TRADE
        );
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}