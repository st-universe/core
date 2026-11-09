<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Component\Realtime\SpacecraftMovementPublisherInterface;
use Stu\Lib\Interaction\InteractionCheckerBuilderFactoryInterface;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Control\StuRandom;
use Stu\Module\Control\StuTime;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Spacecraft\Lib\Auxiliary\SpacecraftShutdownInterface;
use Stu\Orm\Entity\BorgSphereConfiguration;
use Stu\Orm\Entity\BorgSphereEvent;
use Stu\Orm\Entity\BorgSphereRaid;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\BorgSphereRepositoryInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\Orm\Repository\CrewRepositoryInterface;
use Stu\StuTestCase;

final class BorgSphereRaidCooldownTest extends StuTestCase
{
    /** @return iterable<string, array{?int, int}> */
    public static function cooldowns(): iterable
    {
        yield 'no previous attempts' => [null, 0];
        yield 'failed attempts never start a cooldown' => [0, 0];
        yield 'last successful attempt starts two hours' => [1700000000, 1700007200];
    }

    #[DataProvider('cooldowns')]
    public function testCooldownUsesOnlyTheStoredSuccessTimestamp(?int $lastSuccessAt, int $expected): void
    {
        $user = new User();
        $event = new BorgSphereEvent()->setConfiguration(new BorgSphereConfiguration()->setSettings(['raid_cooldown_seconds' => 7200]));
        $raid = $lastSuccessAt === null ? null : new BorgSphereRaid()->setUser($user)->setAttempts(3)->setLastAttemptAt($lastSuccessAt);
        $repository = $this->mock(BorgSphereRepositoryInterface::class);
        $repository->shouldReceive('getRaid')->with($user)->once()->andReturn($raid);
        $random = $this->mock(StuRandom::class);
        $team = new BorgSphereRaidTeam(
            $this->mock(CrewRepositoryInterface::class),
            $this->mock(CrewAssignmentRepositoryInterface::class),
            $this->mock(SpacecraftShutdownInterface::class),
            $this->mock(SpacecraftMovementPublisherInterface::class),
            $random
        );
        $service = new BorgSphereRaidService(
            $repository,
            $team,
            $this->mock(InteractionCheckerBuilderFactoryInterface::class),
            $this->mock(CommodityRepositoryInterface::class),
            $this->mock(StorageManagerInterface::class),
            $this->mock(PrivateMessageSenderInterface::class),
            $random,
            $this->mock(StuTime::class)
        );
        self::assertSame($expected, $service->getCooldownUntil($user, $event));
    }
}
