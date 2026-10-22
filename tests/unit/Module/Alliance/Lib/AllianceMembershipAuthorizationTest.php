<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Lib;

use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Component\Alliance\Enum\AllianceJobPermissionEnum;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\AllianceJob;
use Stu\Orm\Entity\AllianceMemberJob;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\AllianceMemberJobRepositoryInterface;
use Stu\StuTestCase;

final class AllianceMembershipAuthorizationTest extends StuTestCase
{
    public function testJoiningRemovesAllPreviousRoleAssignments(): void
    {
        $user = $this->mock(User::class);
        $alliance = $this->mock(Alliance::class);
        $repository = $this->mock(AllianceMemberJobRepositoryInterface::class);
        $user->shouldReceive('getAlliance')->andReturnNull();
        $user->shouldReceive('getId')->andReturn(101);
        $repository->shouldReceive('truncateByUser')->with(101)->once()->ordered();
        $user->shouldReceive('setAlliance')->with($alliance)->once()->andReturnSelf();
        new AllianceJobManager($repository)->joinAlliance($user, $alliance);
    }

    public function testJoiningRejectsAnExistingMembershipWithoutRemovingRoles(): void
    {
        $user = $this->mock(User::class);
        $alliance = $this->mock(Alliance::class);
        $repository = $this->mock(AllianceMemberJobRepositoryInterface::class);
        $user->shouldReceive('getAlliance')->andReturn($alliance);
        $repository->shouldNotReceive('truncateByUser');
        $user->shouldNotReceive('setAlliance');
        $this->expectException(\Stu\Exception\AccessViolationException::class);
        new AllianceJobManager($repository)->joinAlliance($user, $alliance);
    }

    public static function provideMemberships(): array
    {
        return [[null, false], [42, false], [7, true]];
    }

    #[DataProvider('provideMemberships')]
    public function testOnlyCurrentMembershipAuthorizesAssignments(?int $currentAllianceId, bool $allowed): void
    {
        $user = $this->mock(User::class);
        $alliance = $this->mock(Alliance::class);
        $repository = $this->mock(AllianceMemberJobRepositoryInterface::class);
        $alliance->shouldReceive('getId')->andReturn(7);
        if ($currentAllianceId === null) {
            $user->shouldReceive('getAlliance')->andReturnNull();
        } else {
            $currentAlliance = $this->mock(Alliance::class);
            $currentAlliance->shouldReceive('getId')->andReturn($currentAllianceId);
            $user->shouldReceive('getAlliance')->andReturn($currentAlliance);
        }
        $user->shouldReceive('getId')->andReturn(101);
        if ($allowed) {
            $job = $this->mock(AllianceJob::class);
            $assignment = $this->mock(AllianceMemberJob::class);
            $job->shouldReceive('getAlliance')->andReturn($alliance);
            $job->shouldReceive('hasPermission')->with(1)->andReturnTrue();
            $assignment->shouldReceive('getJob')->andReturn($job);
            $repository->shouldReceive('getByUser')->with(101)->andReturn([$assignment]);
        } else {
            $repository->shouldNotReceive('getByUser');
        }
        $manager = new AllianceJobManager($repository);
        self::assertSame($allowed, $manager->hasUserJob($user, $alliance));
        foreach (AllianceJobPermissionEnum::cases() as $permission) {
            self::assertSame($allowed, $manager->hasUserPermission($user, $alliance, $permission));
        }
    }
}
