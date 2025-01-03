<?php

declare(strict_types=1);

namespace Stu\Module\Station\Action\UndockStationShip;

use Override;
use request;
use Stu\Component\Spacecraft\Repair\CancelRepairInterface;
use Stu\Component\Ship\Retrofit\CancelRetrofitInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StationInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;

final class UndockStationShip implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_UNDOCK_SHIP';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader,
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private PrivateMessageSenderInterface $privateMessageSender,
        private CancelRepairInterface $cancelRepair,
        private CancelRetrofitInterface $cancelRetrofit
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $stationId = request::indInt('id');
        $targetId = request::indInt('target');

        $wrappers = $this->spacecraftLoader->getWrappersBySourceAndUserAndTarget(
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

        if (
            !$target instanceof ShipInterface
            || !$station instanceof StationInterface
            || $target->getDockedTo() !== $station
        ) {
            return;
        }

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
            $target->getHref()
        );

        $this->cancelRepair->cancelRepair($target);
        $this->cancelRetrofit->cancelRetrofit($target);
        $target->setDockedTo(null);
        $target->setDockedToId(null);
        $station->getDockedShips()->remove($target->getId());

        $this->spacecraftRepository->save($target);
        $this->spacecraftLoader->save($station);

        $game->addInformation(sprintf(_('Die %s wurde erfolgreich abgedockt'), $target->getName()));
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
