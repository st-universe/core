<?php

namespace Stu\Lib\Interaction\Member;

use Stu\Component\Anomaly\Type\AnomalyTypeEnum;
use Stu\Lib\Interaction\InteractionCheckType;
use Stu\Module\Ship\Lib\TholianWebUtilInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Entity\User;

class ColonyMember implements InteractionMemberInterface
{
    public function __construct(
        private TholianWebUtilInterface $tholianWebUtil,
        private Colony $colony
    ) {}

    #[\Override]
    public function get(): Colony
    {
        return $this->colony;
    }

    #[\Override]
    public function canAccess(
        InteractionMemberInterface $other,
        callable $shouldCheck
    ): ?InteractionCheckType {

        if (
            $shouldCheck(InteractionCheckType::EXPECT_SOURCE_UNBLOCKED)
            && $this->colony->isBlocked()
        ) {
            return InteractionCheckType::EXPECT_SOURCE_UNBLOCKED;
        }

        return null;
    }

    #[\Override]
    public function canBeAccessedFrom(
        InteractionMemberInterface $other,
        callable $shouldCheck
    ): ?InteractionCheckType {

        if (
            $shouldCheck(InteractionCheckType::EXPECT_TARGET_DOCKED_OR_NO_ION_STORM)
            && $this->colony->getLocation()->hasAnomaly(AnomalyTypeEnum::ION_STORM)
        ) {
            return InteractionCheckType::EXPECT_TARGET_DOCKED_OR_NO_ION_STORM;
        }

        if (
            $shouldCheck(InteractionCheckType::EXPECT_TARGET_ALSO_IN_FINISHED_WEB)
            && $this->tholianWebUtil->isTargetOutsideFinishedTholianWeb($other->get(), $this->colony)
        ) {
            return InteractionCheckType::EXPECT_TARGET_ALSO_IN_FINISHED_WEB;
        }

        if (
            $shouldCheck(InteractionCheckType::EXPECT_TARGET_UNBLOCKED)
            && $this->colony->isBlocked()
        ) {
            return InteractionCheckType::EXPECT_TARGET_UNBLOCKED;
        }

        return null;
    }

    #[\Override]
    public function getLocation(): Map|StarSystemMap
    {
        return $this->colony->getLocation();
    }

    #[\Override]
    public function getUser(): ?User
    {
        return $this->colony->getUser();
    }
}
