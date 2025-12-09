<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Crew;

use RuntimeException;
use Stu\Component\Spacecraft\SpacecraftRumpEnum;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Spacecraft\Lib\Auxiliary\SpacecraftShutdownInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\CrewRepositoryInterface;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRumpRepositoryInterface;

//TODO unit tests
final class SpacecraftLeaver implements SpacecraftLeaverInterface
{
    public function __construct(
        private CrewAssignmentRepositoryInterface $shipCrewRepository,
        private SpacecraftRumpRepositoryInterface $spacecraftRumpRepository,
        private CrewRepositoryInterface $crewRepository,
        private LaunchEscapePodsInterface $launchEscapePods,
        private SpacecraftShutdownInterface $spacecraftShutdown,
        private PrivateMessageSenderInterface $privateMessageSender
    ) {}

    #[\Override]
    public function evacuate(SpacecraftWrapperInterface $wrapper): string
    {
        $ship = $wrapper->get();
        $this->spacecraftShutdown->shutdown($wrapper);

        if ($ship->getRump()->isEscapePods()) {
            $this->letCrewDie($ship);
            return '-- Die Rettungskapseln wurden zerstört, die Crew ist daher verstorben!';
        }

        $podRump = $this->spacecraftRumpRepository->find($ship->getUser()->getFactionId() + SpacecraftRumpEnum::SHIP_RUMP_BASE_ID_ESCAPE_PODS);

        if ($podRump === null || $ship->getUser()->isNpc()) {
            $this->letCrewDie($ship);
            return '-- Keine Rettungskapseln vorhanden, die Crew ist daher verstorben!';
        }

        $this->escapeIntoPods($ship);

        return '-- Die Crew hat das Schiff in den Rettungskapseln verlassen!';
    }

    private function escapeIntoPods(Spacecraft $spacecraft): void
    {
        //create pods entity
        $pods = $this->launchEscapePods->launch($spacecraft);

        //transfer crew into pods
        //TODO not all...! depends on race config
        $crewList = $spacecraft->getCrewAssignments();
        foreach ($crewList as $shipCrew) {
            $shipCrew->setSpacecraft($pods);
            $shipCrew->setSlot(null);
            $this->shipCrewRepository->save($shipCrew);
        }
    }

    #[\Override]
    public function dumpCrewman(CrewAssignment $crewAssignment, string $message): string
    {
        $spacecraft = $crewAssignment->getSpacecraft();
        if ($spacecraft === null) {
            throw new RuntimeException('can only dump crewman on ship');
        }

        //create pods entity
        $pods = $this->launchEscapePods->launch($spacecraft);

        if ($pods == null) {
            $crew = $crewAssignment->getCrew();
            $crewAssignment->clearAssignment();
            $this->shipCrewRepository->delete($crewAssignment);
            $this->crewRepository->delete($crew);

            $survivalMessage = _('Der Crewman wurde exekutiert!');
        } else {

            $spacecraft->getCrewAssignments()->removeElement($crewAssignment);
            $crewAssignment->setSpacecraft($pods);
            $crewAssignment->setSlot(null);

            $this->shipCrewRepository->save($crewAssignment);
            $survivalMessage = _('Der Crewman hat das Schiff in einer Rettungskapsel verlassen!');
        }

        $this->sendPmToOwner(
            $spacecraft->getUser(),
            $crewAssignment->getUser(),
            $message,
            $survivalMessage
        );

        return $survivalMessage;
    }

    private function sendPmToOwner(
        User $sender,
        User $owner,
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

    private function letCrewDie(Spacecraft $spacecraft): void
    {
        $crewArray = [];
        foreach ($spacecraft->getCrewAssignments() as $shipCrew) {
            $crewArray[] = $shipCrew->getCrew();
            $shipCrew->clearAssignment();
            $this->shipCrewRepository->delete($shipCrew);
        }

        foreach ($crewArray as $crew) {
            $this->crewRepository->delete($crew);
        }

        $spacecraft->getCrewAssignments()->clear();
    }
}