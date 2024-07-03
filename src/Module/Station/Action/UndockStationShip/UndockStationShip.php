<?php

declare(strict_types=1);

namespace Stu\Module\Station\Action\UndockStationShip;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use request;
use Stu\Component\Ship\Repair\CancelRepairInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class UndockStationShip implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_UNDOCK_SHIP';

    public function __construct(private ShipLoaderInterface $shipLoader, private PrivateMessageSenderInterface $privateMessageSender, private CancelRepairInterface $cancelRepair, private EntityManagerInterface $entityManager)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $stationId = request::indInt('id');
        $targetId = request::indInt('target');

        $wrappers = $this->shipLoader->getWrappersBySourceAndUserAndTarget(
            $stationId,
            $userId,
            $targetId
        );

        $wrapper = $wrappers->getSource();
        $station = $wrapper->get();

        $targetWrapper = $wrappers->getTarget();
        if ($targetWrapper === null) {
            return;
        }
        $target = $targetWrapper->get();

        if ($target->getDockedTo() !== $station) {
            return;
        }

        if ($target->getUser() !== $game->getUser()) {
            $href = sprintf('ship.php?%s=1&id=%d', ShowShip::VIEW_IDENTIFIER, $target->getId());

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
                PrivateMessageFolderTypeEnum::SPECIAL_SHIP,
                $href
            );
        }

        $this->cancelRepair->cancelRepair($target);
        $target->setDockedTo(null);
        $target->setDockedToId(null);
        $station->getDockedShips()->remove($target->getId());

        $this->shipLoader->save($target);
        $this->shipLoader->save($station);

        $this->entityManager->flush();

        $game->addInformation(sprintf(_('Die %s wurde erfolgreich abgedockt'), $target->getName()));
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
