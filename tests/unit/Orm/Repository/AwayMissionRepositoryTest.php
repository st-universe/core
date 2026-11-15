<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Mockery;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\AwayMission;
use Stu\StuTestCase;

final class AwayMissionRepositoryTest extends StuTestCase
{
    public function testResearchAssignmentsLockOnlyTheMissionWithoutTakingAdditionalUserLocks(): void
    {
        $mission = new AwayMission();
        $em = $this->mock(EntityManagerInterface::class);
        $em->shouldReceive('lock')->once()->with($mission, LockMode::PESSIMISTIC_WRITE);
        $em->shouldNotReceive('createQuery', 'flush');

        new AwayMissionRepository($em)->lockMission($mission);
    }

    public function testAssignmentCountsUseSavedNonNullPlayerChoicesOfTheRequestedMissionOnly(): void
    {
        $mission = new AwayMission();
        $em = $this->mock(EntityManagerInterface::class);
        $query = $this->mock(Query::class);
        $em->shouldReceive('createQuery')->once()->with(Mockery::on(static function (string $dql): bool {
            self::assertStringContainsString('COUNT(p.id)', $dql);
            self::assertStringContainsString('p.mission = :mission', $dql);
            self::assertStringContainsString('p.researchSystem IS NOT NULL', $dql);
            self::assertStringContainsString('IDENTITY(p.user) >= :firstPlayerId', $dql);
            self::assertStringContainsString('GROUP BY p.researchSystem', $dql);
            self::assertStringNotContainsString('rewardReceived', $dql);
            self::assertStringNotContainsString('started', $dql);
            return true;
        }))->andReturn($query);
        $query->shouldReceive('setParameters')->once()->with(['mission' => $mission, 'firstPlayerId' => UserConstants::USER_FIRST_ID])->andReturnSelf();
        $query->shouldReceive('getScalarResult')->once()->andReturn([
            ['system' => 'sensors', 'assignments' => '8'],
            ['system' => 'computer', 'assignments' => 3]
        ]);

        self::assertSame(['sensors' => 8, 'computer' => 3], new AwayMissionRepository($em)->getResearchAssignmentCounts($mission));
    }
}
