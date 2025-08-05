<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\SendBroadcast;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Override;
use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\StationRepositoryInterface;

final class SendBroadcast implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_SEND_BROADCAST';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader,
        private ColonyRepositoryInterface $colonyRepository,
        private StationRepositoryInterface $stationRepository,
        private PrivateMessageSenderInterface $privateMessageSender
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $ship = $this->spacecraftLoader->getByIdAndUser(request::indInt('id'), $game->getUser()->getId());

        $text = request::postStringFatal('text');

        /** @var Collection<int, User> */
        $usersToBroadcast = new ArrayCollection();

        $this->searchBroadcastableColoniesInRange($ship, $usersToBroadcast);
        $this->searchBroadcastableStationsInRange($ship, $usersToBroadcast);

        if ($usersToBroadcast->toArray() == []) {
            $game->getInfo()->addInformation(_("Keine Ziele in Reichweite"));
        } else {
            $this->privateMessageSender->sendBroadcast(
                $ship->getUser(),
                $usersToBroadcast->toArray(),
                $text
            );
            $game->getInfo()->addInformation(_("Der Broadcast wurde erfolgreich versendet"));
        }

        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);
    }

    /**
     * @param Collection<int, User> $usersToBroadcast
     */
    private function searchBroadcastableColoniesInRange(Spacecraft $ship, Collection $usersToBroadcast): void
    {
        $systemMap = $ship->getStarsystemMap();

        if ($systemMap === null) {
            return;
        }

        $colonies = $this->colonyRepository->getForeignColoniesInBroadcastRange($systemMap, $ship->getUser());

        if ($colonies === []) {
            return;
        }

        foreach ($colonies as $colony) {
            if (!$usersToBroadcast->contains($colony->getUser())) {
                $usersToBroadcast->add($colony->getUser());
            }
        }
    }

    /**
     * @param Collection<int, User> $usersToBroadcast
     */
    private function searchBroadcastableStationsInRange(Spacecraft $ship, Collection $usersToBroadcast): void
    {
        $stations = $this->stationRepository->getForeignStationsInBroadcastRange($ship);

        if ($stations === []) {
            return;
        }

        foreach ($stations as $station) {
            if (!$usersToBroadcast->contains($station->getUser())) {
                $usersToBroadcast->add($station->getUser());
            }
        }
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
