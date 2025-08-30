<?php

namespace Stu\Lib\Interaction\Member;

use Override;
use Stu\Component\Anomaly\Type\AnomalyTypeEnum;
use Stu\Component\Spacecraft\Nbs\NbsUtilityInterface;
use Stu\Lib\Interaction\InteractionCheckType;
use Stu\Lib\Transfer\CommodityTransferInterface;
use Stu\Module\Ship\Lib\TholianWebUtilInterface;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Entity\User;

class SpacecraftMember implements InteractionMemberInterface
{
    public function __construct(
        private NbsUtilityInterface $nbsUtility,
        private TholianWebUtilInterface $tholianWebUtil,
        private CommodityTransferInterface $commodityTransfer,
        private Spacecraft $spacecraft
    ) {}

    #[Override]
    public function get(): Spacecraft
    {
        return $this->spacecraft;
    }

    #[Override]
    public function canAccess(
        InteractionMemberInterface $other,
        callable $shouldCheck
    ): ?InteractionCheckType {

        if (
            $shouldCheck(InteractionCheckType::EXPECT_SOURCE_UNSHIELDED)
            && $this->spacecraft->isShielded()
        ) {
            return InteractionCheckType::EXPECT_SOURCE_UNSHIELDED;
        }

        if (
            $shouldCheck(InteractionCheckType::EXPECT_SOURCE_UNCLOAKED)
            && $this->spacecraft->isCloaked()
        ) {
            return InteractionCheckType::EXPECT_SOURCE_UNCLOAKED;
        }

        if (
            $shouldCheck(InteractionCheckType::EXPECT_SOURCE_TACHYON)
            && $other instanceof SpacecraftMember
            && $other->get()->isCloaked()
            && !$this->nbsUtility->isTachyonActive($this->spacecraft)
        ) {
            return InteractionCheckType::EXPECT_SOURCE_TACHYON;
        }

        return null;
    }

    #[Override]
    public function canBeAccessedFrom(
        InteractionMemberInterface $other,
        callable $shouldCheck
    ): ?InteractionCheckType {

        $otherEntity = $other->get();
        if (
            $otherEntity instanceof Spacecraft
            && $shouldCheck(InteractionCheckType::EXPECT_TARGET_DOCKED_OR_NO_ION_STORM)
            && $this->spacecraft->getLocation()->hasAnomaly(AnomalyTypeEnum::ION_STORM)
            && !$this->commodityTransfer->isDockTransfer($this->spacecraft, $otherEntity)
        ) {
            return InteractionCheckType::EXPECT_TARGET_DOCKED_OR_NO_ION_STORM;
        }

        if (
            $shouldCheck(InteractionCheckType::EXPECT_TARGET_SAME_USER)
            && $this->spacecraft->getUser()->getId() !== $other->getUser()?->getId()
        ) {
            return InteractionCheckType::EXPECT_TARGET_SAME_USER;
        }

        if (
            $shouldCheck(InteractionCheckType::EXPECT_TARGET_UNSHIELDED)
            && $this->spacecraft->isShielded()
        ) {
            return InteractionCheckType::EXPECT_TARGET_UNSHIELDED;
        }

        if (
            $shouldCheck(InteractionCheckType::EXPECT_TARGET_UNCLOAKED)
            && $this->spacecraft->isCloaked()
        ) {
            return InteractionCheckType::EXPECT_TARGET_UNCLOAKED;
        }

        if (
            $shouldCheck(InteractionCheckType::EXPECT_TARGET_ALSO_IN_FINISHED_WEB)
            && $this->tholianWebUtil->isTargetOutsideFinishedTholianWeb($other->get(), $this->spacecraft)
        ) {
            return InteractionCheckType::EXPECT_TARGET_ALSO_IN_FINISHED_WEB;
        }

        return null;
    }

    #[Override]
    public function getLocation(): Map|StarSystemMap
    {
        return $this->spacecraft->getLocation();
    }

    #[Override]
    public function getUser(): ?User
    {
        return $this->spacecraft->getUser();
    }
}
