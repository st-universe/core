<?php

declare(strict_types=1);

namespace Stu\Component\Alliance\Event;

use Stu\Component\Alliance\Enum\AllianceRelationTypeEnum;
use Stu\Orm\Entity\Alliance;

/**
 * Describes the event of a diplomatic relation proposal creation
 */
class DiplomaticRelationProposedEvent
{
    public function __construct(
        private readonly Alliance $alliance,
        private readonly Alliance $counterpart,
        private readonly AllianceRelationTypeEnum $relationType
    ) {}

    /**
     * Returns the alliance which created the proposal
     */
    public function getAlliance(): Alliance
    {
        return $this->alliance;
    }

    /**
     * Returns the alliance the relation was proposed to
     */
    public function getCounterpart(): Alliance
    {
        return $this->counterpart;
    }

    /**
     * Returns the proposed relation type
     */
    public function getRelationType(): AllianceRelationTypeEnum
    {
        return $this->relationType;
    }
}
