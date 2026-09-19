<?php

declare(strict_types=1);

namespace Stu\Lib\Interaction;

use Mockery;
use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Interaction\Member\InteractionMemberInterface;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\User;
use Stu\StuTestCase;

class CustomizedInteractionCheckerTest extends StuTestCase
{
    public function testCheckRejectsTargetInVacationMode(): void
    {
        $information = $this->mock(InformationInterface::class);
        $source = $this->mock(InteractionMemberInterface::class);
        $target = $this->mock(InteractionMemberInterface::class);
        $targetUser = $this->mock(User::class);
        $checker = new CustomizedInteractionChecker();

        $checker->setSource($source);
        $checker->setTarget($target);
        $checker->setCheckTypes([
            InteractionCheckType::EXPECT_SOURCE_UNSHIELDED,
            InteractionCheckType::EXPECT_TARGET_UNSHIELDED,
        ]);

        $target->shouldReceive('getUser')->once()->andReturn($targetUser);
        $targetUser->shouldReceive('isVacationRequestOldEnough')->once()->andReturnTrue();
        $information->shouldReceive('addInformation')
            ->with('Aktion nicht möglich, der Spieler befindet sich im Urlaubsmodus!')
            ->once()
            ->andReturn($information);

        $this->assertFalse($checker->check($information));
    }

    public function testCheckRejectsWhenSourceAndTargetAreInDifferentLocations(): void
    {
        $information = $this->mock(InformationInterface::class);
        $source = $this->mock(InteractionMemberInterface::class);
        $target = $this->mock(InteractionMemberInterface::class);
        $targetUser = $this->mock(User::class);
        $sourceLocation = $this->mock(Map::class);
        $targetLocation = $this->mock(Map::class);
        $checker = new CustomizedInteractionChecker();

        $checker->setSource($source);
        $checker->setTarget($target);
        $checker->setCheckTypes([InteractionCheckType::EXPECT_SOURCE_UNSHIELDED]);

        $target->shouldReceive('getUser')->once()->andReturn($targetUser);
        $targetUser->shouldReceive('isVacationRequestOldEnough')->once()->andReturnFalse();
        $source->shouldReceive('getLocation')->once()->andReturn($sourceLocation);
        $target->shouldReceive('getLocation')->once()->andReturn($targetLocation);

        $this->assertFalse($checker->check($information));
    }

    public function testCheckRejectsWhenSourceAccessIsDenied(): void
    {
        $information = $this->mock(InformationInterface::class);
        $source = $this->mock(InteractionMemberInterface::class);
        $target = $this->mock(InteractionMemberInterface::class);
        $targetUser = $this->mock(User::class);
        $location = $this->mock(Map::class);
        $checker = new CustomizedInteractionChecker();

        $checker->setSource($source);
        $checker->setTarget($target);
        $checker->setCheckTypes([InteractionCheckType::EXPECT_SOURCE_UNSHIELDED]);

        $target->shouldReceive('getUser')->once()->andReturn($targetUser);
        $targetUser->shouldReceive('isVacationRequestOldEnough')->once()->andReturnFalse();
        $source->shouldReceive('getLocation')->once()->andReturn($location);
        $target->shouldReceive('getLocation')->once()->andReturn($location);
        $source->shouldReceive('canAccess')
            ->with($target, Mockery::on(function (callable $predicate): bool {
                $this->assertTrue($predicate(InteractionCheckType::EXPECT_SOURCE_UNSHIELDED));
                return true;
            }))
            ->once()
            ->andReturn(InteractionCheckType::EXPECT_SOURCE_UNSHIELDED);
        $information->shouldReceive('addInformation')
            ->with('Die Schilde sind aktiviert')
            ->once()
            ->andReturn($information);
        $target->shouldReceive('canBeAccessedFrom')->never();

        $this->assertFalse($checker->check($information));
    }

    public function testCheckRejectsWhenTargetAccessIsDenied(): void
    {
        $information = $this->mock(InformationInterface::class);
        $source = $this->mock(InteractionMemberInterface::class);
        $target = $this->mock(InteractionMemberInterface::class);
        $targetUser = $this->mock(User::class);
        $location = $this->mock(Map::class);
        $checker = new CustomizedInteractionChecker();

        $checker->setSource($source);
        $checker->setTarget($target);
        $checker->setCheckTypes([InteractionCheckType::EXPECT_TARGET_UNSHIELDED]);

        $target->shouldReceive('getUser')->once()->andReturn($targetUser);
        $targetUser->shouldReceive('isVacationRequestOldEnough')->once()->andReturnFalse();
        $source->shouldReceive('getLocation')->once()->andReturn($location);
        $target->shouldReceive('getLocation')->once()->andReturn($location);
        $source->shouldReceive('canAccess')->with($target, Mockery::on(function (callable $predicate): bool {
            $this->assertTrue($predicate(InteractionCheckType::EXPECT_TARGET_UNSHIELDED));
            return true;
        }))->once()->andReturn(null);
        $target->shouldReceive('canBeAccessedFrom')
            ->with($source, Mockery::on(function (callable $predicate): bool {
                $this->assertTrue($predicate(InteractionCheckType::EXPECT_TARGET_UNSHIELDED));
                return true;
            }))
            ->once()
            ->andReturn(InteractionCheckType::EXPECT_TARGET_UNSHIELDED);
        $information->shouldReceive('addInformation')
            ->with('Das Ziel hat die Schilde aktiviert')
            ->once()
            ->andReturn($information);

        $this->assertFalse($checker->check($information));
    }

    public function testCheckReturnsTrueWhenAllChecksPass(): void
    {
        $information = $this->mock(InformationInterface::class);
        $source = $this->mock(InteractionMemberInterface::class);
        $target = $this->mock(InteractionMemberInterface::class);
        $targetUser = $this->mock(User::class);
        $location = $this->mock(Map::class);
        $checker = new CustomizedInteractionChecker();

        $checker->setSource($source);
        $checker->setTarget($target);
        $checker->setCheckTypes([
            InteractionCheckType::EXPECT_SOURCE_UNSHIELDED,
            InteractionCheckType::EXPECT_TARGET_UNSHIELDED,
        ]);

        $target->shouldReceive('getUser')->once()->andReturn($targetUser);
        $targetUser->shouldReceive('isVacationRequestOldEnough')->once()->andReturnFalse();
        $source->shouldReceive('getLocation')->once()->andReturn($location);
        $target->shouldReceive('getLocation')->once()->andReturn($location);
        $source->shouldReceive('canAccess')->with($target, Mockery::on(function (callable $predicate): bool {
            $this->assertTrue($predicate(InteractionCheckType::EXPECT_SOURCE_UNSHIELDED));
            $this->assertTrue($predicate(InteractionCheckType::EXPECT_TARGET_UNSHIELDED));
            return true;
        }))->once()->andReturn(null);
        $target->shouldReceive('canBeAccessedFrom')->with($source, Mockery::on(function (callable $predicate): bool {
            $this->assertTrue($predicate(InteractionCheckType::EXPECT_SOURCE_UNSHIELDED));
            $this->assertTrue($predicate(InteractionCheckType::EXPECT_TARGET_UNSHIELDED));
            return true;
        }))->once()->andReturn(null);

        $this->assertTrue($checker->check($information));
    }
}
