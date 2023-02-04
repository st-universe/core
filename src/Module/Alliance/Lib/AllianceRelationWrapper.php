<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Lib;

use Stu\Component\Alliance\AllianceEnum;
use Stu\Orm\Entity\AllianceInterface;
use Stu\Orm\Entity\AllianceRelationInterface;

final class AllianceRelationWrapper
{
    private AllianceInterface $alliance;

    private AllianceRelationInterface $relation;

    function __construct(
        AllianceInterface $alliance,
        AllianceRelationInterface $relation
    ) {
        $this->alliance = $alliance;
        $this->relation = $relation;
    }

    public function getDescription(): string
    {
        $typeDescription = $this->relation->getTypeDescription();
        $toName = $this->relation->getOpponent()->getName();
        $fromName = $this->relation->getAlliance()->getName();

        if ($this->relation->getType() === AllianceEnum::ALLIANCE_RELATION_VASSAL) {
            if ($this->relation->getAlliance()->getId() === $this->alliance->getId()) {
                return sprintf('Hat die Allianz %s als %s', $toName, $typeDescription);
            } else {
                return sprintf('Ist %s der Allianz %s', $typeDescription, $fromName);
            }
        }

        if ($this->relation->getAlliance()->getId() === $this->alliance->getId()) {
            return sprintf('%s mit %s', $typeDescription, $toName);
        } else {
            return sprintf('%s mit %s', $typeDescription, $fromName);
        }
    }

    public function getDate(): int
    {
        return $this->relation->getDate();
    }
}
