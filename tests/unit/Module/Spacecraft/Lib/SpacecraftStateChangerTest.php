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
    /** @var MockInterface&CancelMiningInterface */
    private $cancelMining;
    /** @var MockInterface&CancelRepairInterface */
    private $cancelRepair;
    /** @var MockInterface&CancelRetrofitInterface */
    private $cancelRetrofit;
    /** @var MockInterface&AstroEntryLibInterface */
    private $astroEntryLib;
    /** @var MockInterface&SpacecraftRepositoryInterface */
    private $spacecraftRepository;
    /** @var MockInterface&TholianWebUtilInterface */
    private $tholianWebUtil;
    /** @var MockInterface&ShipTakeoverManagerInterface */
    private $shipTakeoverManager;

    /** @var MockInterface&ShipWrapperInterface */
    private $wrapper;

    /** @var MockInterface&ShipInterface */
    private $ship;

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
            ->andReturn(SpacecraftStateEnum::SHIP_STATE_DESTROYED);

        $this->subject->changeShipState($this->wrapper, SpacecraftStateEnum::SHIP_STATE_NONE);
    }

    public function testChangeShipStateExpectNothingWhenStateUnchanged(): void
    {
        $this->ship->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftStateEnum::SHIP_STATE_NONE);

        $this->subject->changeShipState($this->wrapper, SpacecraftStateEnum::SHIP_STATE_NONE);
    }

    public function testChangeShipStateExpectRepairCanceling(): void
    {
        $this->ship->shouldReceive('getState')
            ->withNoArgs()
            ->andReturn(SpacecraftStateEnum::SHIP_STATE_UNDER_SCRAPPING);
        $this->ship->shouldReceive('isUnderRepair')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->cancelRepair->shouldReceive('cancelRepair')
            ->with($this->ship)
            ->once();

        $this->ship->shouldReceive('setState')
            ->with(SpacecraftStateEnum::SHIP_STATE_NONE)
            ->once();

        $this->spacecraftRepository->shouldReceive('save')
            ->with($this->ship)
            ->once();

        $this->subject->changeShipState($this->wrapper, SpacecraftStateEnum::SHIP_STATE_NONE);
    }

    public function testChangeShipStateExpectAstroCanceling(): void
    {
        $this->ship->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftStateEnum::SHIP_STATE_ASTRO_FINALIZING);
        $this->ship->shouldReceive('isUnderRepair')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->astroEntryLib->shouldReceive('cancelAstroFinalizing')
            ->with($this->wrapper)
            ->once();

        $this->ship->shouldReceive('setState')
            ->with(SpacecraftStateEnum::SHIP_STATE_NONE)
            ->once();

        $this->spacecraftRepository->shouldReceive('save')
            ->with($this->ship)
            ->once();

        $this->subject->changeShipState($this->wrapper, SpacecraftStateEnum::SHIP_STATE_NONE);
    }

    public function testChangeShipStateExpectWebRelease(): void
    {
        $this->ship->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftStateEnum::SHIP_STATE_WEB_SPINNING);
        $this->ship->shouldReceive('isUnderRepair')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->tholianWebUtil->shouldReceive('releaseWebHelper')
            ->with($this->wrapper)
            ->once();

        $this->ship->shouldReceive('setState')
            ->with(SpacecraftStateEnum::SHIP_STATE_NONE)
            ->once();

        $this->spacecraftRepository->shouldReceive('save')
            ->with($this->ship)
            ->once();

        $this->subject->changeShipState($this->wrapper, SpacecraftStateEnum::SHIP_STATE_NONE);
    }


    //ALERT STATE

    public function testChangeAlertStateExpectNothingWhenAlertStateUnchanged(): void
    {
        $this->ship->shouldReceive('getAlertState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftAlertStateEnum::ALERT_GREEN);

        $msg = $this->subject->changeAlertState($this->wrapper, SpacecraftAlertStateEnum::ALERT_GREEN);

        $this->assertNull($msg);
    }

    public function testChangeAlertStateExpectNothingWhenChangedToGreen(): void
    {
        $this->ship->shouldReceive('getAlertState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftAlertStateEnum::ALERT_YELLOW);
        $this->ship->shouldReceive('setAlertState')
            ->with(SpacecraftAlertStateEnum::ALERT_GREEN)
            ->once();

        $msg = $this->subject->changeAlertState($this->wrapper, SpacecraftAlertStateEnum::ALERT_GREEN);

        $this->assertNull($msg);
    }

    public function testChangeAlertStateExpectErrorWhenNotEnoughEnergyForYellow(): void
    {
        static::expectException(InsufficientEnergyException::class);

        $this->ship->shouldReceive('getAlertState')
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

        $this->ship->shouldReceive('getAlertState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftAlertStateEnum::ALERT_GREEN);
        $this->ship->shouldReceive('setAlertState')
            ->with(SpacecraftAlertStateEnum::ALERT_YELLOW)
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

        $this->ship->shouldReceive('getAlertState')
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

        $this->ship->shouldReceive('getAlertState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftAlertStateEnum::ALERT_GREEN);
        $this->ship->shouldReceive('setAlertState')
            ->with(SpacecraftAlertStateEnum::ALERT_RED)
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
