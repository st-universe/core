<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Crew;

use Override;
use RuntimeException;
use Stu\Component\Ship\ShipRumpEnum;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\Auxiliary\ShipShutdownInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipCrewInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\CrewRepositoryInterface;
use Stu\Orm\Repository\ShipCrewRepositoryInterface;
use Stu\Orm\Repository\ShipRumpRepositoryInterface;

//TODO unit tests
final class ShipLeaver implements ShipLeaverInterface
{
    public function __construct(private ShipCrewRepositoryInterface $shipCrewRepository, private ShipRumpRepositoryInterface $shipRumpRepository, private CrewRepositoryInterface $crewRepository, private LaunchEscapePodsInterface $launchEscapePods, private ShipShutdownInterface $shipShutdown, private PrivateMessageSenderInterface $privateMessageSender) {}

    #[Override]
    public function evacuate(ShipWrapperInterface $wrapper): string
    {
        $ship = $wrapper->get();
        $this->shipShutdown->shutdown($wrapper);

        if ($ship->getRump()->isEscapePods()) {
            $this->letCrewDie($ship);
            return _('Die Rettungskapseln wurden zerstört, die Crew ist daher verstorben!');
        }

        $podRump = $this->shipRumpRepository->find($ship->getUser()->getFactionId() + ShipRumpEnum::SHIP_RUMP_BASE_ID_ESCAPE_PODS);

        if ($podRump === null || $ship->getUser()->isNpc()) {
            $this->letCrewDie($ship);
            return _('Keine Rettungskapseln vorhanden, die Crew ist daher verstorben!');
        }

        $this->escapeIntoPods($ship);

        return _('Die Crew hat das Schiff in den Rettungskapseln verlassen!');
    }

    private function escapeIntoPods(ShipInterface $ship): void
    {
        //create pods entity
        $pods = $this->launchEscapePods->launch($ship);

        //transfer crew into pods
        //TODO not all...! depends on race config
        $crewList = $ship->getCrewAssignments();
        foreach ($crewList as $shipCrew) {
            $shipCrew->setShip($pods);
            $shipCrew->setSlot(null);
            $this->shipCrewRepository->save($shipCrew);
        }
    }

    #[Override]
    public function dumpCrewman(ShipCrewInterface $shipCrew, string $message): string
    {
        $ship = $shipCrew->getShip();
        if ($ship === null) {
            throw new RuntimeException('can only dump crewman on ship');
        }

        //create pods entity
        $pods = $this->launchEscapePods->launch($ship);

        if ($pods == null) {
            $crew = $shipCrew->getCrew();
            $this->shipCrewRepository->delete($shipCrew);
            $this->crewRepository->delete($crew);

            $survivalMessage = _('Der Crewman wurde exekutiert!');
        } else {

            //transfer crewman into pods
            $shipCrew->setShip($pods);
            $shipCrew->setShipId($pods->getId());
            $ship->getCrewAssignments()->removeElement($shipCrew);
            $this->shipCrewRepository->save($shipCrew);

            $survivalMessage = _('Der Crewman hat das Schiff in einer Rettungskapsel verlassen!');
        }

        $this->sendPmToOwner(
            $ship->getUser(),
            $shipCrew->getUser(),
            $message,
            $survivalMessage
        );

        return $survivalMessage;
    }

    private function sendPmToOwner(
        UserInterface $sender,
        UserInterface $owner,
        string $message,
        string $survivalMessage
    ): void {
        $this->privateMessageSender->send(
            $sender->getId(),
            $owner->getId(),
            sprintf(
                "%s\n%s",
                $message,
                $survivalMessage
            ),
            PrivateMessageFolderTypeEnum::SPECIAL_SYSTEM
        );
    }

    private function letCrewDie(ShipInterface $ship): void
    {
        foreach ($ship->getCrewAssignments() as $shipCrew) {
            $this->crewRepository->delete($shipCrew->getCrew());
            $this->shipCrewRepository->delete($shipCrew);
        }

        $ship->getCrewAssignments()->clear();
    }
}
