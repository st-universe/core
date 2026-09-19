<?php

declare(strict_types=1);

namespace Stu\Lib\Interaction\Member;

use Mockery;
use Mockery\MockInterface;
use Stu\Component\Anomaly\Type\AnomalyTypeEnum;
use Stu\Component\Spacecraft\Nbs\NbsUtilityInterface;
use Stu\Lib\Interaction\InteractionCheckType;
use Stu\Lib\Transfer\CommodityTransferInterface;
use Stu\Module\Ship\Lib\TholianWebUtilInterface;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\User;
use Stu\StuTestCase;

class SpacecraftMemberTest extends StuTestCase
{
    private NbsUtilityInterface&MockInterface $nbsUtility;
    private TholianWebUtilInterface&MockInterface $tholianWebUtil;
    private CommodityTransferInterface&MockInterface $commodityTransfer;
    private Spacecraft&MockInterface $spacecraft;
    private InteractionMemberInterface&MockInterface $other;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->nbsUtility = $this->mock(NbsUtilityInterface::class);
        $this->tholianWebUtil = $this->mock(TholianWebUtilInterface::class);
        $this->commodityTransfer = $this->mock(CommodityTransferInterface::class);
        $this->spacecraft = $this->mock(Spacecraft::class);
        $this->other = $this->mock(InteractionMemberInterface::class);
    }

    public function testGettersReturnTheWrappedSpacecraftInfo(): void
    {
        $subject = new SpacecraftMember($this->nbsUtility, $this->tholianWebUtil, $this->commodityTransfer, $this->spacecraft);

        $this->assertSame($this->spacecraft, $subject->get());
    }

    public function testCanAccessRejectsShieldedSource(): void
    {
        $subject = new SpacecraftMember($this->nbsUtility, $this->tholianWebUtil, $this->commodityTransfer, $this->spacecraft);

        $this->spacecraft->shouldReceive('isShielded')->once()->andReturnTrue();

        $result = $subject->canAccess(
            $this->other,
            fn (InteractionCheckType $type): bool => $type === InteractionCheckType::EXPECT_SOURCE_UNSHIELDED
        );

        $this->assertSame(InteractionCheckType::EXPECT_SOURCE_UNSHIELDED, $result);
    }

    public function testCanAccessRejectsCloakedSource(): void
    {
        $subject = new SpacecraftMember($this->nbsUtility, $this->tholianWebUtil, $this->commodityTransfer, $this->spacecraft);

        $this->spacecraft->shouldReceive('isCloaked')->once()->andReturnTrue();

        $result = $subject->canAccess(
            $this->other,
            fn (InteractionCheckType $type): bool => $type === InteractionCheckType::EXPECT_SOURCE_UNCLOAKED
        );

        $this->assertSame(InteractionCheckType::EXPECT_SOURCE_UNCLOAKED, $result);
    }

    public function testCanAccessRejectsWhenSourceNeedsTachyon(): void
    {
        $otherSpacecraft = $this->mock(Spacecraft::class);
        $other = new SpacecraftMember($this->nbsUtility, $this->tholianWebUtil, $this->commodityTransfer, $otherSpacecraft);
        $subject = new SpacecraftMember($this->nbsUtility, $this->tholianWebUtil, $this->commodityTransfer, $this->spacecraft);

        $otherSpacecraft->shouldReceive('isCloaked')->once()->andReturnTrue();
        $this->nbsUtility->shouldReceive('isTachyonActive')->with($this->spacecraft)->once()->andReturnFalse();

        $result = $subject->canAccess(
            $other,
            fn (InteractionCheckType $type): bool => $type === InteractionCheckType::EXPECT_SOURCE_TACHYON
        );

        $this->assertSame(InteractionCheckType::EXPECT_SOURCE_TACHYON, $result);
    }

    public function testCanAccessRejectsWhenTargetIsNotOnTheSameSideOfFinishedWeb_1(): void
    {
        $otherSpacecraft = $this->mock(Spacecraft::class);
        $subject = new SpacecraftMember($this->nbsUtility, $this->tholianWebUtil, $this->commodityTransfer, $this->spacecraft);

        $this->other->shouldReceive('get')->twice()->andReturn($otherSpacecraft);
        $this->tholianWebUtil->shouldReceive('isTargetInsideFinishedTholianWeb')->with($otherSpacecraft, $this->spacecraft)->once()->andReturnFalse();
        $this->tholianWebUtil->shouldReceive('isTargetInsideFinishedTholianWeb')->with($this->spacecraft, $otherSpacecraft)->once()->andReturnTrue();

        $result = $subject->canAccess(
            $this->other,
            fn (InteractionCheckType $type): bool => $type === InteractionCheckType::EXPECT_TARGET_ON_SAME_SIDE_OF_FINISHED_WEB
        );

        $this->assertSame(InteractionCheckType::EXPECT_TARGET_ON_SAME_SIDE_OF_FINISHED_WEB, $result);
    }

    public function testCanAccessRejectsWhenTargetIsNotOnTheSameSideOfFinishedWeb_2(): void
    {
        $otherSpacecraft = $this->mock(Spacecraft::class);
        $subject = new SpacecraftMember($this->nbsUtility, $this->tholianWebUtil, $this->commodityTransfer, $this->spacecraft);

        $this->other->shouldReceive('get')->once()->andReturn($otherSpacecraft);
        $this->tholianWebUtil->shouldReceive('isTargetInsideFinishedTholianWeb')->with($otherSpacecraft, $this->spacecraft)->once()->andReturnTrue();

        $result = $subject->canAccess(
            $this->other,
            fn (InteractionCheckType $type): bool => $type === InteractionCheckType::EXPECT_TARGET_ON_SAME_SIDE_OF_FINISHED_WEB
        );

        $this->assertSame(InteractionCheckType::EXPECT_TARGET_ON_SAME_SIDE_OF_FINISHED_WEB, $result);
    }

    public function testCanAccessReturnsNullWhenNoRuleMatches(): void
    {
        $subject = new SpacecraftMember($this->nbsUtility, $this->tholianWebUtil, $this->commodityTransfer, $this->spacecraft);

        $this->assertNull($subject->canAccess($this->other, fn (InteractionCheckType $type): bool => false));
    }

    public function testCanBeAccessedFromRejectsIonStormWithoutDockTransfer(): void
    {
        $otherSpacecraft = $this->mock(Spacecraft::class);
        $location = $this->mock(Map::class);
        $subject = new SpacecraftMember($this->nbsUtility, $this->tholianWebUtil, $this->commodityTransfer, $this->spacecraft);

        $this->other->shouldReceive('get')->once()->andReturn($otherSpacecraft);
        $this->spacecraft->shouldReceive('getLocation')->once()->andReturn($location);
        $location->shouldReceive('hasAnomaly')->with(AnomalyTypeEnum::ION_STORM)->once()->andReturnTrue();
        $this->commodityTransfer->shouldReceive('isDockTransfer')->with($this->spacecraft, $otherSpacecraft)->once()->andReturnFalse();

        $result = $subject->canBeAccessedFrom(
            $this->other,
            fn (InteractionCheckType $type): bool => $type === InteractionCheckType::EXPECT_TARGET_DOCKED_OR_NO_ION_STORM
        );

        $this->assertSame(InteractionCheckType::EXPECT_TARGET_DOCKED_OR_NO_ION_STORM, $result);
    }

    public function testCanBeAccessedFromRejectsDifferentUserTargets(): void
    {
        $otherSpacecraft = $this->mock(Spacecraft::class);
        $targetUser = $this->mock(User::class);
        $otherUser = $this->mock(User::class);
        $subject = new SpacecraftMember($this->nbsUtility, $this->tholianWebUtil, $this->commodityTransfer, $this->spacecraft);

        $this->other->shouldReceive('get')->once()->andReturn($otherSpacecraft);
        $this->spacecraft->shouldReceive('getUser')->once()->andReturn($targetUser);
        $targetUser->shouldReceive('getId')->once()->andReturn(1);
        $this->other->shouldReceive('getUser')->once()->andReturn($otherUser);
        $otherUser->shouldReceive('getId')->once()->andReturn(2);

        $result = $subject->canBeAccessedFrom(
            $this->other,
            fn (InteractionCheckType $type): bool => $type === InteractionCheckType::EXPECT_TARGET_SAME_USER
        );

        $this->assertSame(InteractionCheckType::EXPECT_TARGET_SAME_USER, $result);
    }

    public function testCanBeAccessedFromRejectsShieldedTarget(): void
    {
        $otherSpacecraft = $this->mock(Spacecraft::class);
        $subject = new SpacecraftMember($this->nbsUtility, $this->tholianWebUtil, $this->commodityTransfer, $this->spacecraft);

        $this->other->shouldReceive('get')->once()->andReturn($otherSpacecraft);
        $this->spacecraft->shouldReceive('isShielded')->once()->andReturnTrue();

        $result = $subject->canBeAccessedFrom(
            $this->other,
            fn (InteractionCheckType $type): bool => $type === InteractionCheckType::EXPECT_TARGET_UNSHIELDED
        );

        $this->assertSame(InteractionCheckType::EXPECT_TARGET_UNSHIELDED, $result);
    }

    public function testCanBeAccessedFromRejectsCloakedTarget(): void
    {
        $otherSpacecraft = $this->mock(Spacecraft::class);
        $subject = new SpacecraftMember($this->nbsUtility, $this->tholianWebUtil, $this->commodityTransfer, $this->spacecraft);

        $this->other->shouldReceive('get')->once()->andReturn($otherSpacecraft);
        $this->spacecraft->shouldReceive('isCloaked')->once()->andReturnTrue();

        $result = $subject->canBeAccessedFrom(
            $this->other,
            fn (InteractionCheckType $type): bool => $type === InteractionCheckType::EXPECT_TARGET_UNCLOAKED
        );

        $this->assertSame(InteractionCheckType::EXPECT_TARGET_UNCLOAKED, $result);
    }

    public function testCanBeAccessedFromRejectsTargetOutsideFinishedWeb(): void
    {
        $otherSpacecraft = $this->mock(Spacecraft::class);
        $subject = new SpacecraftMember($this->nbsUtility, $this->tholianWebUtil, $this->commodityTransfer, $this->spacecraft);

        $this->other->shouldReceive('get')->twice()->andReturn($otherSpacecraft);
        $this->tholianWebUtil->shouldReceive('isTargetOutsideFinishedTholianWeb')->with($otherSpacecraft, $this->spacecraft)->once()->andReturnTrue();

        $result = $subject->canBeAccessedFrom(
            $this->other,
            fn (InteractionCheckType $type): bool => $type === InteractionCheckType::EXPECT_TARGET_ALSO_IN_FINISHED_WEB
        );

        $this->assertSame(InteractionCheckType::EXPECT_TARGET_ALSO_IN_FINISHED_WEB, $result);
    }

    public function testCanBeAccessedFromReturnsNullWhenNoRuleMatches(): void
    {
        $otherSpacecraft = $this->mock(Spacecraft::class);
        $subject = new SpacecraftMember($this->nbsUtility, $this->tholianWebUtil, $this->commodityTransfer, $this->spacecraft);

        $this->other->shouldReceive('get')->once()->andReturn($otherSpacecraft);

        $this->assertNull($subject->canBeAccessedFrom($this->other, fn (InteractionCheckType $type): bool => false));
    }
}
