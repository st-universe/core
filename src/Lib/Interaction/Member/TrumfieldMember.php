<?php

namespace Stu\Lib\Interaction\Member;

use Stu\Component\Anomaly\Type\AnomalyTypeEnum;
use Stu\Lib\Interaction\InteractionCheckType;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Entity\Trumfield;
use Stu\Orm\Entity\User;

class TrumfieldMember implements InteractionMemberInterface
{
    public function __construct(private Trumfield $trumfield) {}

    #[\Override]
    public function get(): Trumfield
    {
        return $this->trumfield;
    }

    #[\Override]
    public function canAccess(
        InteractionMemberInterface $other,
        callable $shouldCheck
    ): ?InteractionCheckType {
        return null;
    }

    #[\Override]
    public function canBeAccessedFrom(
        InteractionMemberInterface $other,
        callable $shouldCheck
    ): ?InteractionCheckType {

        if (
            $shouldCheck(InteractionCheckType::EXPECT_TARGET_DOCKED_OR_NO_ION_STORM)
            && $this->trumfield->getLocation()->hasAnomaly(AnomalyTypeEnum::ION_STORM)
        ) {
            return InteractionCheckType::EXPECT_TARGET_DOCKED_OR_NO_ION_STORM;
        }

        return null;
    }

    #[\Override]
    public function getLocation(): Map|StarSystemMap
    {
        return $this->trumfield->getLocation();
    }

    #[\Override]
    public function getUser(): ?User
    {
        return null;
    }
}
