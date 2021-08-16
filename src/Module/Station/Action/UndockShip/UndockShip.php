<?php

declare(strict_types=1);

namespace Stu\Module\Station\Action\UndockShip;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class UndockShip implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_UNDOCK_SHIP';

    private ShipLoaderInterface $shipLoader;

    private ShipRepositoryInterface $shipRepository;

    private PrivateMessageSenderInterface $privateMessageSender;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipRepositoryInterface $shipRepository,
        PrivateMessageSenderInterface $privateMessageSender
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipRepository = $shipRepository;
        $this->privateMessageSender = $privateMessageSender;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $station = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $target = $this->shipRepository->find(request::indInt('target'));

        if ($target->getDockedTo() !== $station) {
            return;
        }

        if ($target->getUser() !== $game->getUser()) {

            $href = sprintf(_('ship.php?SHOW_SHIP=1&id=%d'), $target->getId());

            $this->privateMessageSender->send(
                $userId,
                $target->getUser()->getId(),
                sprintf(
                    _('Die %s %s hat die %s in Sektor %s abgedockt'),
                    $station->getRump()->getName(),
                    $station->getName(),
                    $target->getName(),
                    $station->getSectorString()
                ),
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP,
                $href
            );
        }

        $target->cancelRepair();
        $target->setDockedTo(null);
        $station->getDockedShips()->removeElement($target);

        $this->shipRepository->save($station);
        $this->shipRepository->save($target);

        $game->addInformation(sprintf(_('Die %s wurde erfolgreich abgedockt'), $target->getName()));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
