<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Lib;

use Stu\Orm\Entity\AllianceInterface;
use Stu\Orm\Entity\AllianceRelationInterface;
use Stu\Orm\Entity\UserInterface;

final class AllianceRelationItem implements AllianceRelationItemInterface
{
    private AllianceRelationInterface $allianceRelation;

    private UserInterface $currentUser;

    public function __construct(
        AllianceRelationInterface $allianceRelation,
        UserInterface $currentUser
    ) {
        $this->allianceRelation = $allianceRelation;
        $this->currentUser = $currentUser;
    }

    public function getRelation(): AllianceRelationInterface
    {
        return $this->allianceRelation;
    }

    public function getOpponent(): AllianceInterface
    {
        if ($this->allianceRelation->getOpponent() === $this->currentUser->getAlliance()) {
            return $this->allianceRelation->getAlliance();
        }

        return $this->allianceRelation->getOpponent();
    }

    public function offerIsSend(): bool
    {
        return $this->allianceRelation->getAlliance() === $this->currentUser->getAlliance();
    }
}
