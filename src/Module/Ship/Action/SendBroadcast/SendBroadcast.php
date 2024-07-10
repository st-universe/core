<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\SendBroadcast;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Override;
use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class SendBroadcast implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_SEND_BROADCAST';

    public function __construct(private ShipLoaderInterface $shipLoader, private ColonyRepositoryInterface $colonyRepository, private ShipRepositoryInterface $shipRepository, private PrivateMessageSenderInterface $privateMessageSender)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $ship = $this->shipLoader->getByIdAndUser(request::indInt('id'), $game->getUser()->getId());

        $text = request::postStringFatal('text');

        /** @var Collection<int, UserInterface> */
        $usersToBroadcast = new ArrayCollection();

        $this->searchBroadcastableColoniesInRange($ship, $usersToBroadcast);
        $this->searchBroadcastableStationsInRange($ship, $usersToBroadcast);

        if ($usersToBroadcast->toArray() == []) {
            $game->addInformation(_("Keine Ziele in Reichweite"));
        } else {
            $this->privateMessageSender->sendBroadcast(
                $ship->getUser(),
                $usersToBroadcast->toArray(),
                $text
            );
            $game->addInformation(_("Der Broadcast wurde erfolgreich versendet"));
        }

        $game->setView(ShowShip::VIEW_IDENTIFIER);
    }

    /**
     * @param Collection<int, UserInterface> $usersToBroadcast
     */
    private function searchBroadcastableColoniesInRange(ShipInterface $ship, Collection $usersToBroadcast): void
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
     * @param Collection<int, UserInterface> $usersToBroadcast
     */
    private function searchBroadcastableStationsInRange(ShipInterface $ship, Collection $usersToBroadcast): void
    {
        $stations = $this->shipRepository->getForeignStationsInBroadcastRange($ship);

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
