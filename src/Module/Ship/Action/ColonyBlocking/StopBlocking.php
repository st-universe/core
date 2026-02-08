<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\ColonyBlocking;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;

final class StopBlocking implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_STOP_BLOCKING';

    public function __construct(
        private ShipLoaderInterface $shipLoader,
        private PrivateMessageSenderInterface $privateMessageSender
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $currentColony = $ship->isOverColony();
        if ($currentColony === null) {
            return;
        }

        if (!$ship->isFleetLeader()) {
            return;
        }

        $fleet = $ship->getFleet();
        if ($fleet === null || $fleet->getBlockedColony() === null) {
            return;
        }

        $fleet->setBlockedColony(null);

        $text = sprintf(_('Die Flotte %s hat die Blockierung der Kolonie %s aufgehoben'), $fleet->getName(), $currentColony->getName());
        $game->getInfo()->addInformation($text);

        if (!$currentColony->isFree()) {
            $this->privateMessageSender->send(
                $userId,
                $currentColony->getUser()->getId(),
                $text,
                PrivateMessageFolderTypeEnum::SPECIAL_COLONY
            );
        }
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
