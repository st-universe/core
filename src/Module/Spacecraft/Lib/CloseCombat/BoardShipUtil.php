<?php

namespace Stu\Module\Spacecraft\Lib\CloseCombat;

use Stu\Module\Control\StuRandom;
use Stu\Module\Spacecraft\Lib\Auxiliary\SpacecraftShutdownInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageFactoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\Orm\Repository\CrewRepositoryInterface;

class BoardShipUtil implements BoardShipUtilInterface
{
    public function __construct(
        private CrewRepositoryInterface $crewRepository,
        private CrewAssignmentRepositoryInterface $crewAssignmentRepository,
        private CloseCombatUtilInterface $closeCombatUtil,
        private SpacecraftShutdownInterface $spacecraftShutdown,
        private MessageFactoryInterface $messageFactory,
        private StuRandom $stuRandom
    ) {}

    #[\Override]
    public function cycleKillRound(
        array &$attackers,
        array &$defenders,
        SpacecraftWrapperInterface $wrapper,
        SpacecraftWrapperInterface $targetWrapper,
        MessageCollectionInterface $messages
    ): void {

        $ship = $wrapper->get();
        $target = $targetWrapper->get();

        $combatValueAttacker = $this->closeCombatUtil->getCombatValue($attackers, $ship->getUser()->getFaction());
        $combatValueDefender = $this->closeCombatUtil->getCombatValue($defenders, $target->getUser()->getFaction());

        $rand = $this->stuRandom->rand(
            0,
            $combatValueAttacker + $combatValueDefender
        );

        $isDeathOnDefenderSide = $rand <= $combatValueAttacker;
        if ($isDeathOnDefenderSide) {
            $killedCrewAssignment = $this->getKilledCrew($defenders, $targetWrapper);
        } else {
            $killedCrewAssignment = $this->getKilledCrew($attackers, $wrapper);
        }

        $message = $this->messageFactory->createMessage($ship->getUser()->getId(), $killedCrewAssignment->getUser()->getId());
        $message->add(sprintf(
            '%s %s des Spielers %s von der %s wurde im Kampf getötet.',
            $killedCrewAssignment->getCrew()->getType()->getDescription(),
            $killedCrewAssignment->getCrew()->getName(),
            $isDeathOnDefenderSide ? $target->getUser()->getName() : $ship->getUser()->getName(),
            $isDeathOnDefenderSide ? $target->getName() : $ship->getName()
        ));
        $messages->add($message);
    }

    /**
     * @param array<int, CrewAssignment> &$combatGroup
     */
    private function getKilledCrew(array &$combatGroup, SpacecraftWrapperInterface $wrapper): CrewAssignment
    {
        $keys = array_keys($combatGroup);
        shuffle($keys);

        $randomKey = current($keys);

        $killedCrewAssignment = $combatGroup[$randomKey];
        unset($combatGroup[$randomKey]);

        $ship = $wrapper->get();
        $ship->getCrewAssignments()->removeElement($killedCrewAssignment);
        $crew = $killedCrewAssignment->getCrew();
        $killedCrewAssignment->clearAssignment();
        $this->crewAssignmentRepository->delete($killedCrewAssignment);
        $this->crewRepository->delete($crew);

        if ($ship->getCrewAssignments()->isEmpty()) {
            $this->spacecraftShutdown->shutdown($wrapper, true);
        }

        return $killedCrewAssignment;
    }
}
