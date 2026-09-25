<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Lib;

use Stu\Component\Alliance\Enum\AllianceRelationTypeEnum;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\Relation;
use Stu\Orm\Entity\User;

final class AllianceRelationWrapper
{
    public function __construct(
        private Alliance $alliance,
        private Relation $relation
    ) {}

    public function getDescription(): string
    {
        return $this->getDescriptionPrefix()
            . $this->getCounterpart()->getName()
            . $this->getDescriptionSuffix();
    }

    public function getCounterpart(): Alliance|User
    {
        return $this->isSourceAlliance()
            ? $this->relation->getRecipientParty()
            : $this->relation->getSourceParty();
    }

    public function getCounterpartUrl(): string
    {
        $counterpart = $this->getCounterpart();

        return sprintf(
            $counterpart instanceof Alliance ? 'alliance.php?id=%d' : 'userprofile.php?uid=%d',
            $counterpart->getId()
        );
    }

    public function getDescriptionPrefix(): string
    {
        $typeDescription = $this->relation->getType()->getDescription();
        if ($this->relation->getType() === AllianceRelationTypeEnum::VASSAL) {
            if ($this->isSourceAlliance()) {
                return $this->getCounterpart() instanceof Alliance ? 'Hat die Allianz ' : 'Hat den Siedler ';
            }

            return sprintf(
                $this->getCounterpart() instanceof Alliance ? 'Ist %s der Allianz ' : 'Ist %s des Siedlers ',
                $typeDescription
            );
        }

        return sprintf('%s mit ', $typeDescription);
    }

    public function getDescriptionSuffix(): string
    {
        return $this->relation->getType() === AllianceRelationTypeEnum::VASSAL && $this->isSourceAlliance()
            ? sprintf(' als %s', $this->relation->getType()->getDescription())
            : '';
    }

    private function isSourceAlliance(): bool
    {
        return $this->relation->getSourceAlliance()?->getId() === $this->alliance->getId();
    }

    public function getDate(): int
    {
        return $this->relation->getDate();
    }

    public function getType(): AllianceRelationTypeEnum
    {
        return $this->relation->getType();
    }

    public function getId(): int
    {
        return $this->relation->getId();
    }

    public function hasText(): bool
    {
        return $this->relation->hasText();
    }
}
