<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Crew;

use RuntimeException;
use Stu\Component\Ship\ShipRumpEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\AstroEntryLibInterface;
use Stu\Module\Ship\Lib\Crew\LaunchEscapePodsInterface;
use Stu\Module\Ship\Lib\Fleet\LeaveFleetInterface;
use Stu\Module\Ship\Lib\Interaction\ShipTakeoverManagerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipCrewInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\CrewRepositoryInterface;
use Stu\Orm\Repository\ShipCrewRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\ShipRumpRepositoryInterface;

//TODO unit tests
final class ShipLeaver implements ShipLeaverInterface
{
    private ShipCrewRepositoryInterface $shipCrewRepository;

    private ShipRepositoryInterface $shipRepository;

    private ShipRumpRepositoryInterface $shipRumpRepository;

    private ShipSystemManagerInterface $shipSystemManager;

    private CrewRepositoryInterface $crewRepository;

    private AstroEntryLibInterface $astroEntryLib;

    private LeaveFleetInterface $leaveFleet;

    private LaunchEscapePodsInterface $launchEscapePods;

    private ShipTakeoverManagerInterface $shipTakeoverManager;

    private PrivateMessageSenderInterface $privateMessageSender;

    public function __construct(
        ShipCrewRepositoryInterface $shipCrewRepository,
        ShipRepositoryInterface $shipRepository,
        ShipRumpRepositoryInterface $shipRumpRepository,
        ShipSystemManagerInterface $shipSystemManager,
        CrewRepositoryInterface $crewRepository,
        AstroEntryLibInterface $astroEntryLib,
        LeaveFleetInterface $leaveFleet,
        LaunchEscapePodsInterface $launchEscapePods,
        ShipTakeoverManagerInterface $shipTakeoverManager,
        PrivateMessageSenderInterface $privateMessageSender
    ) {
        $this->shipCrewRepository = $shipCrewRepository;
        $this->shipRepository = $shipRepository;
        $this->shipRumpRepository = $shipRumpRepository;
        $this->shipSystemManager = $shipSystemManager;
        $this->crewRepository = $crewRepository;
        $this->astroEntryLib = $astroEntryLib;
        $this->leaveFleet = $leaveFleet;
        $this->launchEscapePods = $launchEscapePods;
        $this->shipTakeoverManager = $shipTakeoverManager;
        $this->privateMessageSender = $privateMessageSender;
    }

    public function evacuate(ShipWrapperInterface $wrapper): string
    {
        $ship = $wrapper->get();
        $this->shutdown($wrapper);

        if ($ship->getRump()->isEscapePods()) {
            $this->letCrewDie($ship);
            return _('Die Rettungskapseln wurden zerstört, die Crew ist daher verstorben!');
        }

        $podRump = $this->shipRumpRepository->find($ship->getUser()->getFactionId() + ShipRumpEnum::SHIP_RUMP_BASE_ID_ESCAPE_PODS);

        if ($podRump === null) {
            $this->letCrewDie($ship);
            return _('Keine Rettungskapseln vorhanden, die Crew ist daher verstorben!');
        }

        $this->escapeIntoPods($ship);

        return _('Die Crew hat das Schiff in den Rettungskapseln verlassen!');
    }

    public function shutdown(ShipWrapperInterface $wrapper): void
    {
        $this->shipSystemManager->deactivateAll($wrapper);

        $ship = $wrapper->get();

        $this->leaveFleet->leaveFleet($ship);

        if ($ship->getDockedShipCount() > 0) {
            $this->undockShips($ship);
        }

        if ($ship->getState() === ShipStateEnum::SHIP_STATE_ASTRO_FINALIZING) {
            $this->astroEntryLib->cancelAstroFinalizing($ship);
        }

        // clear any ShipTakeover
        $this->shipTakeoverManager->cancelTakeover(
            $ship->getTakeoverActive()
        );

        $ship->setAlertStateGreen();
        $ship->setDockedTo(null);
        $this->shipRepository->save($ship);
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
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_SYSTEM
        );
    }

    private function letCrewDie(ShipInterface $ship): void
    {
        $crewArray = [];
        foreach ($ship->getCrewAssignments() as $shipCrew) {
            $crewArray[] = $shipCrew->getCrew();
        }

        $this->shipCrewRepository->truncateByShip($ship->getId());

        foreach ($crewArray as $crew) {
            $this->crewRepository->delete($crew);
        }
    }

    private function undockShips(ShipInterface $ship): void
    {
        foreach ($ship->getDockedShips() as $dockedShip) {
            $dockedShip->setDockedTo(null);
            $this->shipRepository->save($dockedShip);
        }
    }
}
