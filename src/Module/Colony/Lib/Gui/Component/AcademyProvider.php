<?php

namespace Stu\Module\Colony\Lib\Gui\Component;

use Stu\Component\Crew\CrewCountRetrieverInterface;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Template\TemplateInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\User;

final class AcademyProvider implements PlanetFieldHostComponentInterface
{
    public function __construct(private ColonyLibFactoryInterface $colonyLibFactory, private CrewCountRetrieverInterface $crewCountRetriever) {}

    /** @param Colony&PlanetFieldHostInterface $entity */
    #[\Override]
    public function setTemplateVariables(
        $entity,
        TemplateInterface $template,
        User $user
    ): void {
        $crewInTrainingCount = $this->crewCountRetriever->getInTrainingCount($user);
        $crewRemainingCount = $this->crewCountRetriever->getRemainingCount($user);
        $crewTrainableCount = $this->crewCountRetriever->getTrainableCount($user);

        $trainableCrew = $crewTrainableCount - $crewInTrainingCount;
        if ($trainableCrew > $crewRemainingCount) {
            $trainableCrew = $crewRemainingCount;
        }

        if ($trainableCrew > $entity->getChangeable()->getWorkless()) {
            $trainableCrew = $entity->getChangeable()->getWorkless();
        }

        $freeAssignmentCount = $this->colonyLibFactory->createColonyPopulationCalculator(
            $entity
        )->getFreeAssignmentCount();

        $localcrewlimit = $this->colonyLibFactory->createColonyPopulationCalculator(
            $entity
        )->getCrewLimit();

        $crewinlocalpool = $entity->getCrewAssignmentAmount();

        if ($localcrewlimit - $crewinlocalpool < $trainableCrew) {
            $trainableCrew = $localcrewlimit - $crewinlocalpool;
        }

        if ($trainableCrew > $freeAssignmentCount) {
            $trainableCrew = $freeAssignmentCount;
        }

        if ($trainableCrew < 0) {
            $trainableCrew = 0;
        }

        $template->setTemplateVar('TRAINABLE_CREW_COUNT_PER_TICK', $trainableCrew);
        $template->setTemplateVar(
            'CREW_COUNT_TRAINING',
            $crewInTrainingCount
        );
        $template->setTemplateVar(
            'CREW_COUNT_REMAINING',
            $crewRemainingCount
        );
        $template->setTemplateVar(
            'CREW_COUNT_TRAINABLE',
            $crewTrainableCount
        );
    }
}
