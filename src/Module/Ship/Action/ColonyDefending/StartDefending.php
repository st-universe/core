<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\ColonyDefending;

use request;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;

final class StartDefending implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_START_DEFENDING';

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

        if ($currentColony->isFree()) {
            return;
        }

        $fleet = $ship->getFleet();
        if ($fleet === null ||  $fleet->getBlockedColony() !== null || $fleet->getDefendedColony() !== null) {
            return;
        }

        if ($currentColony->getUser()->isVacationRequestOldEnough()) {
            $game->getInfo()->addInformation(_('Aktion nicht möglich, der Spieler befindet sich im Urlaubsmodus!'));
            return;
        }

        if ($currentColony->isBlocked()) {
            $game->getInfo()->addInformation(_('Aktion nicht möglich, die Kolonie wird blockiert!'));
            return;
        }

        $fleet->setDefendedColony($currentColony);

        $text = sprintf(_('Die Kolonie %s wird nun von der Flotte %s verteidigt'), $currentColony->getName(), $fleet->getName());
        $game->getInfo()->addInformation($text);

        $this->privateMessageSender->send(
            $userId,
            $currentColony->getUser()->getId(),
            $text,
            PrivateMessageFolderTypeEnum::SPECIAL_COLONY
        );
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
