<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\SendBroadcast;

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

        $text = request::postStringFatal('text');

        $usersToBroadcast = array_merge(
            $this->searchBroadcastableColoniesInRange($ship),
            $this->searchBroadcastableStationsInRange($ship)
        );

        if ($usersToBroadcast === []) {
            $game->addInformation(_("Keine Ziele in Reichweite"));
        } else {
            $this->privateMessageSender->sendBroadcast(
                $ship->getUser(),
                $usersToBroadcast,
                $text
            );
            $game->addInformation(_("Der Broadcast wurde erfolgreich versendet"));
        }

        $game->setView(ShowShip::VIEW_IDENTIFIER);
    }

    /**
     * @return UserInterface[]
     */
    private function searchBroadcastableColoniesInRange(ShipInterface $ship): array
    {
        $systemMap = $ship->getStarsystemMap();

        if ($systemMap === null) {
            return [];
        }

        $colonies = $this->colonyRepository->getForeignColoniesInBroadcastRange($systemMap, $ship->getUser());

        if ($colonies === []) {
            return [];
        }

        $result = [];
        foreach ($colonies as $colony) {
            $result[$colony->getUser()->getId()] = $colony->getUser();
        }

        return $result;
    }

    /**
     * @return UserInterface[]
     */
    private function searchBroadcastableStationsInRange(ShipInterface $ship): array
    {
        $stations = $this->shipRepository->getForeignStationsInBroadcastRange($ship);

        if ($stations === []) {
            return [];
        }

        $result = [];
        foreach ($stations as $station) {
            $result[$station->getUser()->getId()] = $station->getUser();
        }

        return $result;
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
