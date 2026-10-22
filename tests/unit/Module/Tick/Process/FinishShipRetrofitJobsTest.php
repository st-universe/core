<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Process;

use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Module\Ship\Lib\ShipRetrofitInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonyShipQueue;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\SpacecraftCondition;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\ColonyShipQueueRepositoryInterface;
use Stu\StuTestCase;

final class FinishShipRetrofitJobsTest extends StuTestCase
{
    #[DataProvider('changedRelationships')]
    public function testInvalidFinishedJobCannotInstallModulesAndDoesNotBlockOtherJobs(string $change): void
    {
        $invalid = $this->createJob($change);
        $valid = $this->createJob();
        $retrofit = $this->mock(ShipRetrofitInterface::class);
        $retrofit->shouldReceive('updateBy')->with($valid->getShip(), $valid->getSpacecraftBuildplan(), $valid->getColony())->once();
        $queues = $this->mock(ColonyShipQueueRepositoryInterface::class);
        $queues->shouldReceive('getFinishedJobs')->once()->andReturn([$invalid, $valid]);
        $queues->shouldReceive('delete')->with($invalid)->once();
        $queues->shouldReceive('delete')->with($valid)->once();

        new FinishShipRetrofitJobs($retrofit, $queues)->work();

        self::assertSame(SpacecraftStateEnum::NONE, $valid->getShip()->getCondition()->getState());
        if ($invalid->getShip() !== null) {
            self::assertSame(
                $change === 'owner' ? SpacecraftStateEnum::RETROFIT : SpacecraftStateEnum::NONE,
                $invalid->getShip()->getCondition()->getState()
            );
        }
    }

    public static function changedRelationships(): iterable
    {
        foreach (['missing', 'owner', 'colonyOwner', 'location', 'rump', 'newRump', 'newPlanOwner', 'oldPlanOwner', 'state'] as $change) {
            yield [$change];
        }
    }

    public function testAuthorizedFinishedJobInstallsSelectedPlan(): void
    {
        $job = $this->createJob();
        $retrofit = $this->mock(ShipRetrofitInterface::class);
        $retrofit->shouldReceive('updateBy')->with($job->getShip(), $job->getSpacecraftBuildplan(), $job->getColony())->once();
        $queues = $this->mock(ColonyShipQueueRepositoryInterface::class);
        $queues->shouldReceive('getFinishedJobs')->once()->andReturn([$job]);
        $queues->shouldReceive('delete')->with($job)->once();

        new FinishShipRetrofitJobs($retrofit, $queues)->work();

        self::assertSame(SpacecraftStateEnum::NONE, $job->getShip()->getCondition()->getState());
    }

    private function createJob(string $change = ''): ColonyShipQueue
    {
        $user = $this->mock(User::class);
        $user->shouldReceive('getId')->andReturn(42);
        $foreignUser = $this->mock(User::class);
        $foreignUser->shouldReceive('getId')->andReturn(99);
        $rump = $this->mock(SpacecraftRump::class);
        $rump->shouldReceive('getId')->andReturn(6);
        $otherRump = $this->mock(SpacecraftRump::class);
        $otherRump->shouldReceive('getId')->andReturn(7);
        $plan = new SpacecraftBuildplan()->setRump($change === 'newRump' ? $otherRump : $rump)
            ->setUser($change === 'newPlanOwner' ? $foreignUser : $user);
        $oldPlan = new SpacecraftBuildplan()->setRump($rump)
            ->setUser($change === 'oldPlanOwner' ? $foreignUser : $user);
        $location = new StarSystemMap();
        $colony = $this->mock(Colony::class);
        $colony->shouldReceive('getUserId')->andReturn($change === 'colonyOwner' ? 99 : 42);
        $colony->shouldReceive('getLocation')->andReturn($location);
        $ship = $this->mock(Ship::class);
        $condition = new SpacecraftCondition($ship)->setState($change === 'state' ? SpacecraftStateEnum::NONE : SpacecraftStateEnum::RETROFIT);
        $ship->shouldReceive('getUserId')->andReturn($change === 'owner' ? 99 : 42);
        $ship->shouldReceive('getLocation')->andReturn($change === 'location' ? new StarSystemMap() : $location);
        $ship->shouldReceive('getRump')->andReturn($change === 'rump' ? $otherRump : $rump);
        $ship->shouldReceive('getBuildplan')->andReturn($oldPlan);
        $ship->shouldReceive('getState')->andReturnUsing(fn () => $condition->getState());
        $ship->shouldReceive('getCondition')->andReturn($condition);

        return new ColonyShipQueue()->setUserId(42)->setRump($rump)->setSpacecraftBuildplan($plan)
            ->setColony($colony)->setShip($change === 'missing' ? null : $ship)->setMode(2);
    }
}
