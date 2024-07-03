<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Lib;

use Override;
use Stu\Orm\Entity\AllianceInterface;
use Stu\Orm\Entity\AllianceRelationInterface;
use Stu\Orm\Entity\UserInterface;

final class AllianceRelationItem implements AllianceRelationItemInterface
{
    public function __construct(private AllianceRelationInterface $allianceRelation, private UserInterface $currentUser)
    {
    }

    #[Override]
    public function getRelation(): AllianceRelationInterface
    {
        return $this->allianceRelation;
    }

    #[Override]
    public function getOpponent(): AllianceInterface
    {
        if ($this->allianceRelation->getOpponent() === $this->currentUser->getAlliance()) {
            return $this->allianceRelation->getAlliance();
        }

        return $this->allianceRelation->getOpponent();
    }

    #[Override]
    public function offerIsSend(): bool
    {
        return $this->allianceRelation->getAlliance() === $this->currentUser->getAlliance();
    }
}
