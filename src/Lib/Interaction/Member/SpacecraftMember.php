<?php

namespace Stu\Lib\Interaction\Member;

use Override;
use Stu\Component\Spacecraft\Nbs\NbsUtilityInterface;
use Stu\Lib\Interaction\InteractionCheckType;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Entity\UserInterface;

class SpacecraftMember implements InteractionMemberInterface
{
    public function __construct(
        private NbsUtilityInterface $nbsUtility,
        private SpacecraftInterface $spacecraft
    ) {}

    #[Override]
    public function canAccess(
        InteractionMemberInterface $other,
        callable $shouldCheck
    ): ?InteractionCheckType {

        if (
            $shouldCheck(InteractionCheckType::EXPECT_SOURCE_UNSHIELDED)
            && $this->spacecraft->getShieldState()
        ) {
            return InteractionCheckType::EXPECT_SOURCE_UNSHIELDED;
        }

        if (
            $shouldCheck(InteractionCheckType::EXPECT_SOURCE_UNCLOAKED)
            && $this->spacecraft->getCloakState()
        ) {
            return InteractionCheckType::EXPECT_SOURCE_UNCLOAKED;
        }

        if (
            $shouldCheck(InteractionCheckType::EXPECT_SOURCE_TACHYON)
            && $other instanceof SpacecraftInterface
            && $other->getCloakState()
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

        if (
            $shouldCheck(InteractionCheckType::EXPECT_TARGET_UNSHIELDED)
            && $this->spacecraft->getShieldState() && !$isFriend
        ) {
            return InteractionCheckType::EXPECT_TARGET_UNSHIELDED;
        }

        if (
            $shouldCheck(InteractionCheckType::EXPECT_TARGET_UNCLOAKED)
            && $this->spacecraft->getCloakState()
        ) {
            return InteractionCheckType::EXPECT_TARGET_UNCLOAKED;
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