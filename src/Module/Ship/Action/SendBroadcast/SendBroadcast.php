<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\SendBroadcast;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class SendBroadcast implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_SEND_BROADCAST';

    private ShipLoaderInterface $shipLoader;

    private ColonyRepositoryInterface $colonyRepository;

    private ShipRepositoryInterface $shipRepository;

    private PrivateMessageSenderInterface $privateMessageSender;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ColonyRepositoryInterface $colonyRepository,
        ShipRepositoryInterface $shipRepository,
        PrivateMessageSenderInterface $privateMessageSender
    ) {
        $this->shipLoader = $shipLoader;
        $this->colonyRepository = $colonyRepository;
        $this->shipRepository = $shipRepository;
        $this->privateMessageSender = $privateMessageSender;
    }

    public function handle(GameControllerInterface $game): void
    {
        $ship = $this->shipLoader->getByIdAndUser(request::indInt('id'), $game->getUser()->getId());

        if (!request::postStringFatal('text')) {
            return;
        }

        $hasFoundRecipient = $this->broadcastToColoniesInRange($ship);
        $hasFoundRecipient = $hasFoundRecipient  && $this->broadcastToStationsInRange($ship);

        if ($hasFoundRecipient) {
            $game->addInformation(_("Der Broadcast wurde erfolgreich versendet"));
        } else {
            $game->addInformation(_("Keine Ziele in Reichweite"));
        }

        $game->setView(ShowShip::VIEW_IDENTIFIER);
    }

    private function broadcastToColoniesInRange(ShipInterface $ship): bool
    {
        $systemMap = $ship->getStarsystemMap();

        if ($systemMap === null) {
            return false;
        }

        $colonies = $this->colonyRepository->getForeignColoniesInBroadcastRange($ship);

        if (empty($colonies)) {
            return false;
        }

        foreach ($colonies as $colony) {
            $this->sendMessage($ship, $colony->getUser()->getId());
        }

        return true;
    }

    private function broadcastToStationsInRange(ShipInterface $ship): bool
    {
        $stations = $this->shipRepository->getForeignStationsInBroadcastRange($ship);

        if (empty($stations)) {
            return false;
        }

        foreach ($stations as $station) {
            $this->sendMessage($ship, $station->getUser()->getId());
        }

        return true;
    }

    private function sendMessage(ShipInterface $ship, int $recipientId): void
    {
        $this->privateMessageSender->send(
            $ship->getUser()->getId(),
            $recipientId,
            request::postStringFatal('text'),
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_MAIN
        );
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
