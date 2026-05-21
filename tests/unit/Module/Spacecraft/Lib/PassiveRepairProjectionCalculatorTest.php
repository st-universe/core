<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib;

use Mockery\MockInterface;
use Stu\Component\Spacecraft\Repair\RepairUtilInterface;
use Stu\Module\Control\StuTime;
use Stu\Orm\Entity\ColonyShipRepair;
use Stu\Orm\Entity\Ship;
use Stu\StuTestCase;

class PassiveRepairProjectionCalculatorTest extends StuTestCase
{
    private MockInterface&RepairUtilInterface $repairUtil;
    private MockInterface&StuTime $stuTime;

    private PassiveRepairProjectionCalculator $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->repairUtil = $this->mock(RepairUtilInterface::class);
        $this->stuTime = $this->mock(StuTime::class);

        $this->subject = new PassiveRepairProjectionCalculator(
            $this->repairUtil,
            $this->stuTime
        );
    }

    public function testCalculateProjectsQueuedFinishTimes(): void
    {
        $job1 = $this->mock(ColonyShipRepair::class);
        $job2 = $this->mock(ColonyShipRepair::class);
        $ship1 = $this->mock(Ship::class);
        $ship2 = $this->mock(Ship::class);

        $this->stuTime->shouldReceive('time')
            ->withNoArgs()
            ->once()
            ->andReturn(1_000);

        $job1->shouldReceive('isStopped')->zeroOrMoreTimes()->andReturnFalse();
        $job1->shouldReceive('getShip')->zeroOrMoreTimes()->andReturn($ship1);
        $job1->shouldReceive('getFinishTime')->zeroOrMoreTimes()->andReturn(1_060);
        $job1->shouldReceive('getStopDate')->zeroOrMoreTimes()->andReturn(0);
        $job1->shouldReceive('getShipId')->zeroOrMoreTimes()->andReturn(1);

        $job2->shouldReceive('isStopped')->zeroOrMoreTimes()->andReturnFalse();
        $job2->shouldReceive('getShip')->zeroOrMoreTimes()->andReturn($ship2);
        $job2->shouldReceive('getFinishTime')->zeroOrMoreTimes()->andReturn(0);
        $job2->shouldReceive('getStopDate')->zeroOrMoreTimes()->andReturn(0);
        $job2->shouldReceive('getShipId')->zeroOrMoreTimes()->andReturn(2);

        $this->repairUtil->shouldReceive('getPassiveRepairStepDuration')
            ->with($ship1)
            ->once()
            ->andReturn(100);
        $this->repairUtil->shouldReceive('getPassiveRepairStepDuration')
            ->with($ship2)
            ->once()
            ->andReturn(80);
        $this->repairUtil->shouldReceive('getPassiveRepairEstimatedDurationForSpacecraft')
            ->with($ship1, false)
            ->once()
            ->andReturn(300);
        $this->repairUtil->shouldReceive('getPassiveRepairEstimatedDurationForSpacecraft')
            ->with($ship2, false)
            ->once()
            ->andReturn(200);

        $result = $this->subject->calculate([$job1, $job2], 1, false);

        $this->assertSame(1_060, $result[1]->getPotentialNextWaveTime());
        $this->assertSame(1_260, $result[1]->getPotentialFinishTime());
        $this->assertTrue($result[1]->isActiveRepair());

        $this->assertSame(1_340, $result[2]->getPotentialNextWaveTime());
        $this->assertSame(1_460, $result[2]->getPotentialFinishTime());
        $this->assertFalse($result[2]->isActiveRepair());
    }

    public function testCalculateLeavesFinishTimesOpenForStoppedScope(): void
    {
        $job = $this->mock(ColonyShipRepair::class);

        $this->stuTime->shouldReceive('time')
            ->withNoArgs()
            ->once()
            ->andReturn(1_000);

        $job->shouldReceive('isStopped')->zeroOrMoreTimes()->andReturnTrue();
        $job->shouldReceive('getFinishTime')->zeroOrMoreTimes()->andReturn(1_060);
        $job->shouldReceive('getStopDate')->zeroOrMoreTimes()->andReturn(1_000);
        $job->shouldReceive('getShipId')->zeroOrMoreTimes()->andReturn(1);

        $this->repairUtil->shouldNotReceive('getPassiveRepairStepDuration');
        $this->repairUtil->shouldNotReceive('getPassiveRepairEstimatedDurationForSpacecraft');

        $result = $this->subject->calculate([$job], 1, false);

        $this->assertSame(0, $result[1]->getPotentialNextWaveTime());
        $this->assertSame(0, $result[1]->getPotentialFinishTime());
        $this->assertFalse($result[1]->isActiveRepair());
    }
}
