<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Lib;

use Stu\Component\Alliance\Enum\AllianceRelationTypeEnum;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\AllianceRelation;

final class AllianceRelationWrapper
{
    public function __construct(private Alliance $alliance, private AllianceRelation $relation) {}

    public function getDescription(): string
    {
        $typeDescription = $this->relation->getType()->getDescription();
        $toName = $this->relation->getOpponent()->getName();
        $fromName = $this->relation->getAlliance()->getName();

        if ($this->relation->getType() === AllianceRelationTypeEnum::VASSAL) {
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

    public function getType(): AllianceRelationTypeEnum
    {
        return $this->relation->getType();
    }
}
