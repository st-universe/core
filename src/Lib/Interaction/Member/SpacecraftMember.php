<?php

namespace Stu\Lib\Interaction\Member;

use Override;
use Stu\Component\Anomaly\Type\AnomalyTypeEnum;
use Stu\Component\Spacecraft\Nbs\NbsUtilityInterface;
use Stu\Lib\Interaction\InteractionCheckType;
use Stu\Lib\Transfer\CommodityTransferInterface;
use Stu\Module\Ship\Lib\TholianWebUtilInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Entity\UserInterface;

class SpacecraftMember implements InteractionMemberInterface
{
    public function __construct(
        private NbsUtilityInterface $nbsUtility,
        private TholianWebUtilInterface $tholianWebUtil,
        private CommodityTransferInterface $commodityTransfer,
        private SpacecraftInterface $spacecraft
    ) {}

    #[Override]
    public function get(): SpacecraftInterface
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
        bool $isFriend,
        callable $shouldCheck
    ): ?InteractionCheckType {

        $otherEntity = $other->get();
        if (
            $otherEntity instanceof SpacecraftInterface
            && $shouldCheck(InteractionCheckType::EXPECT_TARGET_DOCKED_OR_NO_ION_STORM)
            && $this->spacecraft->getLocation()->hasAnomaly(AnomalyTypeEnum::ION_STORM)
            && !$this->commodityTransfer->isDockTransfer($this->spacecraft, $otherEntity)
        ) {
            return InteractionCheckType::EXPECT_TARGET_DOCKED_OR_NO_ION_STORM;
        }

        if (
            $shouldCheck(InteractionCheckType::EXPECT_TARGET_SAME_USER)
            && $this->spacecraft->getUser() !== $other->getUser()
        ) {
            return InteractionCheckType::EXPECT_TARGET_SAME_USER;
        }

        if (
            $shouldCheck(InteractionCheckType::EXPECT_TARGET_UNSHIELDED)
            && $this->spacecraft->isShielded() && !$isFriend
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
    public function getLocation(): MapInterface|StarSystemMapInterface
    {
        return $this->spacecraft->getLocation();
    }

    #[Override]
    public function getUser(): ?UserInterface
    {
        return $this->spacecraft->getUser();
    }
}
