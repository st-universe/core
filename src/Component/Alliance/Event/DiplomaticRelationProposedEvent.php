<?php

declare(strict_types=1);

namespace Stu\Component\Alliance\Event;

use Stu\Orm\Entity\AllianceInterface;

/**
 * Describes the event of a diplomatic relation proposal creation
 */
class DiplomaticRelationProposedEvent
{
    private AllianceInterface $alliance;

    private AllianceInterface $counterpart;

    private int $relationTypeId;

    public function __construct(
        AllianceInterface $alliance,
        AllianceInterface $counterpart,
        int $relationTypeId
    ) {
        $this->alliance = $alliance;
        $this->counterpart = $counterpart;
        $this->relationTypeId = $relationTypeId;
    }

    /**
     * Returns the alliance which created the proposal
     */
    public function getAlliance(): AllianceInterface
    {
        return $this->alliance;
    }

    /**
     * Returns the alliance the relation was proposed to
     */
    public function getCounterpart(): AllianceInterface
    {
        return $this->counterpart;
    }

    /**
     * Returns the proposed relation type
     */
    public function getRelationTypeId(): int
    {
        return $this->relationTypeId;
    }
}