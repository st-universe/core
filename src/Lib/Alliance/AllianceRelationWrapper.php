<?php

namespace Lib\Alliance;

use Stu\Component\Alliance\AllianceEnum;
use Stu\Orm\Entity\AllianceInterface;
use Stu\Orm\Entity\AllianceRelationInterface;

class AllianceRelationWrapper
{

    private $alliance = null;
    private $relation = null;

    function __construct(AllianceInterface $alliance, AllianceRelationInterface $relation)
    {
        $this->alliance = $alliance;
        $this->relation = $relation;
    }

    public function getDescription(): string
    {
        $typeDescription = $this->relation->getTypeDescription();
        $toName = $this->relation->getOpponent()->getName();
        $fromName = $this->relation->getAlliance()->getName();

        if ($this->relation->getType() === AllianceEnum::ALLIANCE_RELATION_VASSAL) {
            if ($this->relation->getAlliance() === $this->alliance) {
                return sprintf('Hat die Allianz %s als %s', $toName, $typeDescription);
            } else {
                return sprintf('Ist %s der Allianz %s', $typeDescription, $fromName);
            }
        }

        if ($this->relation->getAlliance() === $this->alliance) {
            return sprintf('%s mit %s', $typeDescription, $toName);
        } else {
            return sprintf('%s mit %s', $typeDescription, $fromName);
        }
    }

    public function getTargetId(): int
    {
        return $this->relation->getAlliance() === $this->alliance ? $this->relation->getOpponent()->getId() : $this->alliance->getId();
    }
}
