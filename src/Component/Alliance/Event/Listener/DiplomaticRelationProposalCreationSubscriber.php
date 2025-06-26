<?php

declare(strict_types=1);

namespace Stu\Component\Alliance\Event\Listener;

use Stu\Component\Alliance\AllianceEnum;
use Stu\Component\Alliance\Event\DiplomaticRelationProposedEvent;
use Stu\Component\Alliance\Event\WarDeclaredEvent;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Repository\AllianceRelationRepositoryInterface;

/**
 * Subscribes to events related to diplomatic relations proposals
 */
final class DiplomaticRelationProposalCreationSubscriber
{
    public function __construct(private AllianceRelationRepositoryInterface $allianceRelationRepository, private AllianceActionManagerInterface $allianceActionManager)
    {
    }

    /**
     * Reacts on war declaration events
     */
    public function onWarDeclaration(
        WarDeclaredEvent $event
    ): void {
        $alliance = $event->getAlliance();
        $counterpart = $event->getCounterpart();

        $this->allianceRelationRepository->truncateByAlliances($alliance, $counterpart);

        $this->createAllianceRelation(
            $alliance,
            $counterpart,
            AllianceEnum::ALLIANCE_RELATION_WAR,
            time()
        );

        $this->allianceActionManager->sendMessage(
            $counterpart->getId(),
            sprintf('Die Allianz %s hat Deiner Allianz den Krieg erklärt', $alliance->getName())
        );
    }

    /**
     * Reacts on diplomatic relation proposals
     */
    public function onRelationProposal(
        DiplomaticRelationProposedEvent $event
    ): void {
        $alliance = $event->getAlliance();
        $counterpart = $event->getCounterpart();

        $this->createAllianceRelation(
            $alliance,
            $counterpart,
            $event->getRelationTypeId()
        );

        $this->allianceActionManager->sendMessage(
            $counterpart->getId(),
            sprintf(
                'Die Allianz %s hat Deiner Allianz ein Abkommen angeboten',
                $alliance->getName()
            )
        );
    }

    private function createAllianceRelation(
        Alliance $alliance,
        Alliance $counterpart,
        int $relationTypeId,
        int $date = 0
    ): void {
        $relation = $this->allianceRelationRepository
            ->prototype()
            ->setAlliance($alliance)
            ->setOpponent($counterpart)
            ->setType($relationTypeId)
            ->setDate($date);

        $this->allianceRelationRepository->save($relation);
    }
}
