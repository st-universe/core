<?php

declare(strict_types=1);

namespace Stu\Module\Station\Action\UndockStationShip;

use request;
use Doctrine\ORM\EntityManagerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class UndockStationShip implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_UNDOCK_SHIP';

    private ShipLoaderInterface $shipLoader;

    private PrivateMessageSenderInterface $privateMessageSender;

    private EntityManagerInterface $entityManager;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        PrivateMessageSenderInterface $privateMessageSender,
        EntityManagerInterface $entityManager
    ) {
        $this->shipLoader = $shipLoader;
        $this->privateMessageSender = $privateMessageSender;
        $this->entityManager = $entityManager;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $station = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $target = $this->shipLoader->find(request::indInt('target'));

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
        $target->setDockedToId(null);
        $station->getDockedShips()->remove($target->getId());

        $this->shipLoader->save($target);
        $this->shipLoader->save($station);

        $this->entityManager->flush();

        $game->addInformation(sprintf(_('Die %s wurde erfolgreich abgedockt'), $target->getName()));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
