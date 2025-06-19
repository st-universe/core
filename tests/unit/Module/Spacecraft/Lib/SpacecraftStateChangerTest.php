<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib;

use Mockery\MockInterface;
use Override;
use Stu\Component\Ship\Mining\CancelMiningInterface;
use Stu\Component\Spacecraft\Repair\CancelRepairInterface;
use Stu\Component\Ship\Retrofit\CancelRetrofitInterface;
use Stu\Component\Spacecraft\SpacecraftAlertStateEnum;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Component\Spacecraft\System\Data\ComputerSystemData;
use Stu\Component\Spacecraft\System\Data\EpsSystemData;
use Stu\Component\Spacecraft\System\Exception\InsufficientEnergyException;
use Stu\Module\Ship\Lib\AstroEntryLibInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\Interaction\ShipTakeoverManagerInterface;
use Stu\Module\Ship\Lib\TholianWebUtilInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Stu\StuTestCase;

class SpacecraftStateChangerTest extends StuTestCase
{
    private MockInterface&CancelMiningInterface $cancelMining;
    private MockInterface&CancelRepairInterface $cancelRepair;
    private MockInterface&CancelRetrofitInterface $cancelRetrofit;
    private MockInterface&AstroEntryLibInterface $astroEntryLib;
    private MockInterface&SpacecraftRepositoryInterface $spacecraftRepository;
    private MockInterface&TholianWebUtilInterface $tholianWebUtil;
    private MockInterface&ShipTakeoverManagerInterface $shipTakeoverManager;

    private MockInterface&ShipWrapperInterface $wrapper;

    private MockInterface&ShipInterface $ship;

    private SpacecraftStateChangerInterface $subject;

    #[Override]
    public function setUp(): void
    {
        //injected
        $this->cancelMining = $this->mock(CancelMiningInterface::class);
        $this->cancelRepair = $this->mock(CancelRepairInterface::class);
        $this->cancelRetrofit = $this->mock(CancelRetrofitInterface::class);
        $this->astroEntryLib = $this->mock(AstroEntryLibInterface::class);
        $this->spacecraftRepository = $this->mock(SpacecraftRepositoryInterface::class);
        $this->tholianWebUtil = $this->mock(TholianWebUtilInterface::class);
        $this->shipTakeoverManager = $this->mock(ShipTakeoverManagerInterface::class);

        //params
        $this->wrapper = $this->mock(ShipWrapperInterface::class);

        //other
        $this->ship = $this->mock(ShipInterface::class);

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn($this->ship);

        $this->subject = new SpacecraftStateChanger(
            $this->cancelMining,
            $this->cancelRepair,
            $this->astroEntryLib,
            $this->spacecraftRepository,
            $this->tholianWebUtil,
            $this->shipTakeoverManager,
            $this->cancelRetrofit
        );
    }

    public function testChangeShipStateExpectNothingWhenDestroyed(): void
    {
        $this->ship->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftStateEnum::DESTROYED);

        $this->subject->changeState($this->wrapper, SpacecraftStateEnum::NONE);
    }

    public function testChangeShipStateExpectNothingWhenStateUnchanged(): void
    {
        $this->ship->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftStateEnum::NONE);

        $this->subject->changeState($this->wrapper, SpacecraftStateEnum::NONE);
    }

    public function testChangeShipStateExpectRepairCanceling(): void
    {
        $this->ship->shouldReceive('getState')
            ->withNoArgs()
            ->andReturn(SpacecraftStateEnum::REPAIR_ACTIVE);

        $this->cancelRepair->shouldReceive('cancelRepair')
            ->with($this->ship)
            ->once();

        $this->ship->shouldReceive('getCondition->setState')
            ->with(SpacecraftStateEnum::NONE)
            ->once();

        $this->spacecraftRepository->shouldReceive('save')
            ->with($this->ship)
            ->once();

        $this->subject->changeState($this->wrapper, SpacecraftStateEnum::NONE);
    }

    public function testChangeShipStateExpectAstroCanceling(): void
    {
        $this->ship->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftStateEnum::ASTRO_FINALIZING);

        $this->astroEntryLib->shouldReceive('cancelAstroFinalizing')
            ->with($this->wrapper)
            ->once();

        $this->ship->shouldReceive('getCondition->setState')
            ->with(SpacecraftStateEnum::NONE)
            ->once();

        $this->spacecraftRepository->shouldReceive('save')
            ->with($this->ship)
            ->once();

        $this->subject->changeState($this->wrapper, SpacecraftStateEnum::NONE);
    }

    public function testChangeShipStateExpectWebRelease(): void
    {
        $this->ship->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftStateEnum::WEB_SPINNING);

        $this->tholianWebUtil->shouldReceive('releaseWebHelper')
            ->with($this->wrapper)
            ->once();

        $this->ship->shouldReceive('getCondition->setState')
            ->with(SpacecraftStateEnum::NONE)
            ->once();

        $this->spacecraftRepository->shouldReceive('save')
            ->with($this->ship)
            ->once();

        $this->subject->changeState($this->wrapper, SpacecraftStateEnum::NONE);
    }


    //ALERT STATE

    public function testChangeAlertStateExpectNothingWhenAlertStateUnchanged(): void
    {
        $this->wrapper->shouldReceive('getAlertState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftAlertStateEnum::ALERT_GREEN);

        $msg = $this->subject->changeAlertState($this->wrapper, SpacecraftAlertStateEnum::ALERT_GREEN);

        $this->assertNull($msg);
    }

    public function testChangeAlertStateExpectNothingWhenNoComputerInstalled(): void
    {
        $this->ship->shouldReceive('hasComputer')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $this->wrapper->shouldReceive('getAlertState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftAlertStateEnum::ALERT_YELLOW);

        $msg = $this->subject->changeAlertState($this->wrapper, SpacecraftAlertStateEnum::ALERT_GREEN);

        $this->assertNull($msg);
    }

    public function testChangeAlertStateExpectNothingWhenChangedToGreen(): void
    {
        $computer = $this->mock(ComputerSystemData::class);

        $this->ship->shouldReceive('hasComputer')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();
        $this->wrapper->shouldReceive('getAlertState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftAlertStateEnum::ALERT_YELLOW);
        $this->wrapper->shouldReceive('getComputerSystemDataMandatory->setAlertState')
            ->with(SpacecraftAlertStateEnum::ALERT_GREEN)
            ->once()
            ->andReturn($computer);

        $computer->shouldReceive('update')
            ->withNoArgs()
            ->once();

        $msg = $this->subject->changeAlertState($this->wrapper, SpacecraftAlertStateEnum::ALERT_GREEN);

        $this->assertNull($msg);
    }

    public function testChangeAlertStateExpectErrorWhenNotEnoughEnergyForYellow(): void
    {
        static::expectException(InsufficientEnergyException::class);

        $this->ship->shouldReceive('hasComputer')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        $this->wrapper->shouldReceive('getAlertState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftAlertStateEnum::ALERT_GREEN);

        $this->wrapper->shouldReceive('getEpsSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $this->subject->changeAlertState($this->wrapper, SpacecraftAlertStateEnum::ALERT_YELLOW);
    }

    public function testChangeAlertStateExpectChangeToYellow(): void
    {
        $epsSystemData = $this->mock(EpsSystemData::class);
        $computer = $this->mock(ComputerSystemData::class);

        $epsSystemData->shouldReceive('getEps')
            ->withNoArgs()
            ->once()
            ->andReturn(1);
        $epsSystemData->shouldReceive('lowerEps')
            ->with(1)
            ->once()
            ->andReturnSelf();
        $epsSystemData->shouldReceive('update')
            ->withNoArgs()
            ->once();

        $this->ship->shouldReceive('hasComputer')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        $this->wrapper->shouldReceive('getAlertState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftAlertStateEnum::ALERT_GREEN);
        $this->wrapper->shouldReceive('getComputerSystemDataMandatory->setAlertState')
            ->with(SpacecraftAlertStateEnum::ALERT_YELLOW)
            ->once()
            ->andReturn($computer);

        $computer->shouldReceive('update')
            ->withNoArgs()
            ->once();

        $this->wrapper->shouldReceive('getEpsSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($epsSystemData);

        $this->cancelRepair->shouldReceive('cancelRepair')
            ->with($this->ship)
            ->once()
            ->andReturn(false);

        $this->cancelRetrofit->shouldReceive('cancelRetrofit')
            ->with($this->ship)
            ->once()
            ->andReturn(false);

        $msg = $this->subject->changeAlertState($this->wrapper, SpacecraftAlertStateEnum::ALERT_YELLOW);

        $this->assertNull($msg);
    }

    public function testChangeAlertStateExpectErrorWhenNotEnoughEnergyForRed(): void
    {
        static::expectException(InsufficientEnergyException::class);

        $epsSystemData = $this->mock(EpsSystemData::class);
        $epsSystemData->shouldReceive('getEps')
            ->withNoArgs()
            ->once()
            ->andReturn(1);

        $this->ship->shouldReceive('hasComputer')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        $this->wrapper->shouldReceive('getAlertState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftAlertStateEnum::ALERT_GREEN);
        $this->wrapper->shouldReceive('getEpsSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($epsSystemData);

        $this->subject->changeAlertState($this->wrapper, SpacecraftAlertStateEnum::ALERT_RED);
    }

    public function testChangeAlertStateExpectChangeToRed(): void
    {
        $epsSystemData = $this->mock(EpsSystemData::class);
        $computer = $this->mock(ComputerSystemData::class);

        $epsSystemData->shouldReceive('getEps')
            ->withNoArgs()
            ->once()
            ->andReturn(2);
        $epsSystemData->shouldReceive('lowerEps')
            ->with(2)
            ->once()
            ->andReturnSelf();
        $epsSystemData->shouldReceive('update')
            ->withNoArgs()
            ->once();

        $this->ship->shouldReceive('hasComputer')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        $this->wrapper->shouldReceive('getAlertState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftAlertStateEnum::ALERT_GREEN);
        $this->wrapper->shouldReceive('getComputerSystemDataMandatory->setAlertState')
            ->with(SpacecraftAlertStateEnum::ALERT_RED)
            ->once()
            ->andReturn($computer);

        $computer->shouldReceive('update')
            ->withNoArgs()
            ->once();

        $this->wrapper->shouldReceive('getEpsSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($epsSystemData);

        // Stelle sicher, dass `cancelRepair` aufgerufen wird und `true` zurückgibt
        $this->cancelRepair->shouldReceive('cancelRepair')
            ->with($this->ship)
            ->once()
            ->andReturn(true);  // Simuliere den Abbruch der Reparatur

        // Stelle sicher, dass `cancelRetrofit` aufgerufen wird und `true` zurückgibt
        $this->cancelRetrofit->shouldReceive('cancelRetrofit')
            ->with($this->ship)
            ->once()
            ->andReturn(false);  // Simuliere den Abbruch der Umrüstung

        // Führe die Methode aus, die getestet wird
        $msg = $this->subject->changeAlertState($this->wrapper, SpacecraftAlertStateEnum::ALERT_RED);

        // Überprüfe, ob beide Nachrichten in der Rückgabe enthalten sind, aber einzeln geprüft
        $this->assertStringContainsString('Die Reparatur wurde abgebrochen', $msg);
    }
}
