<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Lib;

use Stu\Component\Alliance\AllianceEnum;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\AllianceRelation;

final class AllianceRelationWrapper
{
    public function __construct(private Alliance $alliance, private AllianceRelation $relation)
    {
    }

    public function getDescription(): string
    {
        $typeDescription = AllianceEnum::relationTypeToDescription($this->relation->getType());
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

    /**
     * Returns the image name for relation type visualization
     */
    public function getImage(): string
    {
        return match ($this->relation->getType()) {
            AllianceEnum::ALLIANCE_RELATION_WAR => 'war_negative',
            AllianceEnum::ALLIANCE_RELATION_PEACE, AllianceEnum::ALLIANCE_RELATION_FRIENDS => 'friendship_positive',
            AllianceEnum::ALLIANCE_RELATION_ALLIED => 'alliance_positive',
            AllianceEnum::ALLIANCE_RELATION_TRADE => 'trade_positive',
            AllianceEnum::ALLIANCE_RELATION_VASSAL => 'vassal_positive',
            default => '',
        };
    }
}
