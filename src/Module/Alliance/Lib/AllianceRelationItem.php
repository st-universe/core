<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Lib;

use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\AllianceRelation;
use Stu\Orm\Entity\User;

final class AllianceRelationItem implements AllianceRelationItemInterface
{
    public function __construct(private AllianceRelation $allianceRelation, private User $currentUser)
    {
    }

    #[\Override]
    public function getRelation(): AllianceRelation
    {
        return $this->allianceRelation;
    }

    #[\Override]
    public function getOpponent(): Alliance
    {
        if ($this->allianceRelation->getOpponent() === $this->currentUser->getAlliance()) {
            return $this->allianceRelation->getAlliance();
        }

        return $this->allianceRelation->getOpponent();
    }

    #[\Override]
    public function offerIsSend(): bool
    {
        return $this->allianceRelation->getAlliance() === $this->currentUser->getAlliance();
    }
}
