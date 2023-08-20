<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component;

use Stu\Component\Ship\System\Data\EpsSystemData;
use Stu\Component\Ship\System\Data\WarpDriveSystemData;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\StuTestCase;

class ShipMovementBlockingDeterminatorTest extends StuTestCase
{
    private ShipMovementBlockingDeterminator $subject;

    protected function setUp(): void
    {
        $this->subject = new ShipMovementBlockingDeterminator();
    }

    public function testDetermineFailsDueToMissingCrew(): void
    {
        $shipWrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(ShipInterface::class);

        $shipName = 'some-name';

        $shipWrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($ship);

        $ship->shouldReceive('hasEnoughCrew')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $ship->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn($shipName);

        static::assertSame(
            [
                sprintf(
                    'Die %s hat ungenügend Crew',
                    $shipName
                ),
            ],
            $this->subject->determine([$shipWrapper])
        );
    }

    public function testDetermineFailsDueToEnergyShortageBecauseOfTractorBeam(): void
    {
        $shipWrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $tractorBeamTarget = $this->mock(ShipInterface::class);
        $epsSystemData = $this->mock(EpsSystemData::class);
        $warpDriveSystemData = $this->mock(WarpDriveSystemData::class);

        $shipName = 'some-name';
        $tractorBeamTargetEnergyCostPerField = 666;
        $energyCostPerField = 42;
        $WarpDrive = 666;

        $shipWrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($ship);
        $shipWrapper->shouldReceive('getEpsSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($epsSystemData);
        $shipWrapper->shouldReceive('getWarpDriveSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($warpDriveSystemData);


        $ship->shouldReceive('hasEnoughCrew')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();
        $ship->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn($shipName);
        $ship->shouldReceive('getRump->getFlightEcost')
            ->withNoArgs()
            ->once()
            ->andReturn($energyCostPerField);
        $ship->shouldReceive('getTractoringShip')
            ->withNoArgs()
            ->once()
            ->andReturn($tractorBeamTarget);

        $epsSystemData->shouldReceive('getEps')
            ->withNoArgs()
            ->once()
            ->andReturn($tractorBeamTargetEnergyCostPerField + $energyCostPerField - 1);

        $tractorBeamTarget->shouldReceive('getRump->getFlightEcost')
            ->withNoArgs()
            ->once()
            ->andReturn($tractorBeamTargetEnergyCostPerField);
        $warpDriveSystemData->shouldReceive('getWarpDrive')
            ->withNoArgs()
            ->once()
            ->andReturn($WarpDrive);

        static::assertSame(
            [
                sprintf(
                    'Die %s hat nicht genug Energie für den Traktor-Flug (%d benötigt)',
                    $shipName,
                    $tractorBeamTargetEnergyCostPerField + $energyCostPerField
                ),
            ],
            $this->subject->determine([$shipWrapper])
        );
    }

    public function testDetermineFailsDueToEnergyShortageForFlight(): void
    {
        $shipWrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $epsSystemData = $this->mock(EpsSystemData::class);
        $warpDriveSystemData = $this->mock(WarpDriveSystemData::class);


        $shipName = 'some-name';
        $energyCostPerField = 42;
        $WarpDrive = 666;

        $shipWrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($ship);
        $shipWrapper->shouldReceive('getEpsSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($epsSystemData);
        $shipWrapper->shouldReceive('getWarpDriveSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($warpDriveSystemData);

        $ship->shouldReceive('hasEnoughCrew')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();
        $ship->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn($shipName);
        $ship->shouldReceive('getRump->getFlightEcost')
            ->withNoArgs()
            ->once()
            ->andReturn($energyCostPerField);
        $ship->shouldReceive('getTractoringShip')
            ->withNoArgs()
            ->once()
            ->andReturnNull();

        $epsSystemData->shouldReceive('getEps')
            ->withNoArgs()
            ->once()
            ->andReturn($energyCostPerField - 1);
        $warpDriveSystemData->shouldReceive('getWarpDrive')
            ->withNoArgs()
            ->once()
            ->andReturn($WarpDrive);

        static::assertSame(
            [
                sprintf(
                    'Die %s hat nicht genug Energie für den Flug (%d benötigt)',
                    $shipName,
                    $energyCostPerField
                ),
            ],
            $this->subject->determine([$shipWrapper])
        );
    }

    public function testDetermineReturnsEmptyListIfTheresNoHoldup(): void
    {
        $shipWrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $epsSystemData = $this->mock(EpsSystemData::class);
        $warpDriveSystemData = $this->mock(WarpDriveSystemData::class);


        $energyCostPerField = 42;
        $WarpDrive = 666;

        $shipWrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($ship);
        $shipWrapper->shouldReceive('getEpsSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($epsSystemData);
        $shipWrapper->shouldReceive('getWarpDriveSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($warpDriveSystemData);

        $ship->shouldReceive('hasEnoughCrew')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();
        $ship->shouldReceive('getRump->getFlightEcost')
            ->withNoArgs()
            ->once()
            ->andReturn($energyCostPerField);
        $ship->shouldReceive('getTractoringShip')
            ->withNoArgs()
            ->once()
            ->andReturnNull();

        $epsSystemData->shouldReceive('getEps')
            ->withNoArgs()
            ->once()
            ->andReturn($energyCostPerField);
        $warpDriveSystemData->shouldReceive('getWarpDrive')
            ->withNoArgs()
            ->once()
            ->andReturn($WarpDrive);


        static::assertSame(
            [],
            $this->subject->determine([$shipWrapper])
        );
    }
}
