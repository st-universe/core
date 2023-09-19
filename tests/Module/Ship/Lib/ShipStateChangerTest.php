<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Mockery\MockInterface;
use Stu\Component\Ship\Repair\CancelRepairInterface;
use Stu\Component\Ship\ShipAlertStateEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\Data\EpsSystemData;
use Stu\Component\Ship\System\Exception\InsufficientEnergyException;
use Stu\Module\Control\StuTime;
use Stu\Module\Ship\Lib\AstroEntryLibInterface;
use Stu\Module\Ship\Lib\ShipStateChanger;
use Stu\Module\Ship\Lib\ShipStateChangerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Ship\Lib\TholianWebUtilInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\StuTestCase;

class ShipStateChangerTest extends StuTestCase
{
    /** @var MockInterface|CancelRepairInterface */
    private CancelRepairInterface $cancelRepair;

    /** @var MockInterface|AstroEntryLibInterface */
    private AstroEntryLibInterface $astroEntryLib;

    /** @var MockInterface|ShipRepositoryInterface */
    private ShipRepositoryInterface $shipRepository;

    /** @var MockInterface|TholianWebUtilInterface */
    private TholianWebUtilInterface $tholianWebUtil;

    /** @var MockInterface|StuTime */
    private StuTime $stuTime;

    /** @var MockInterface|ShipWrapperInterface */
    private ShipWrapperInterface $wrapper;

    /** @var MockInterface|ShipInterface */
    private ShipInterface $ship;

    private ShipStateChangerInterface $subject;

    public function setUp(): void
    {
        //injected
        $this->cancelRepair = $this->mock(CancelRepairInterface::class);
        $this->astroEntryLib = $this->mock(AstroEntryLibInterface::class);
        $this->shipRepository = $this->mock(ShipRepositoryInterface::class);
        $this->tholianWebUtil = $this->mock(TholianWebUtilInterface::class);
        $this->stuTime = $this->mock(StuTime::class);

        //params
        $this->wrapper = $this->mock(ShipWrapperInterface::class);

        //other
        $this->ship = $this->mock(ShipInterface::class);

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn($this->ship);

        $this->subject = new ShipStateChanger(
            $this->cancelRepair,
            $this->astroEntryLib,
            $this->shipRepository,
            $this->tholianWebUtil,
            $this->stuTime
        );
    }

    public function testChangeShipStateExpectNothingWhenDestroyed(): void
    {
        $this->ship->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(ShipStateEnum::SHIP_STATE_DESTROYED);

        $this->subject->changeShipState($this->wrapper, -42);
    }

    public function testChangeShipStateExpectNothingWhenStateUnchanged(): void
    {
        $this->ship->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(-42);

        $this->subject->changeShipState($this->wrapper, -42);
    }

    public function testChangeShipStateExpectRepairCanceling(): void
    {
        $this->ship->shouldReceive('getState')
            ->withNoArgs()
            ->andReturn(5);
        $this->ship->shouldReceive('isUnderRepair')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->cancelRepair->shouldReceive('cancelRepair')
            ->with($this->ship)
            ->once();

        $this->ship->shouldReceive('setState')
            ->with(-42)
            ->once();

        $this->shipRepository->shouldReceive('save')
            ->with($this->ship)
            ->once();

        $this->subject->changeShipState($this->wrapper, -42);
    }

    public function testChangeShipStateExpectAstroCanceling(): void
    {
        $this->ship->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(ShipStateEnum::SHIP_STATE_ASTRO_FINALIZING);
        $this->ship->shouldReceive('isUnderRepair')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->astroEntryLib->shouldReceive('cancelAstroFinalizing')
            ->with($this->ship)
            ->once();

        $this->ship->shouldReceive('setState')
            ->with(-42)
            ->once();

        $this->shipRepository->shouldReceive('save')
            ->with($this->ship)
            ->once();

        $this->subject->changeShipState($this->wrapper, -42);
    }

    public function testChangeShipStateExpectWebRelease(): void
    {
        $this->ship->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(ShipStateEnum::SHIP_STATE_WEB_SPINNING);
        $this->ship->shouldReceive('isUnderRepair')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->tholianWebUtil->shouldReceive('releaseWebHelper')
            ->with($this->wrapper)
            ->once();

        $this->ship->shouldReceive('setState')
            ->with(-42)
            ->once();

        $this->shipRepository->shouldReceive('save')
            ->with($this->ship)
            ->once();

        $this->subject->changeShipState($this->wrapper, -42);
    }


    //ALERT STATE

    public function testChangeAlertStateExpectNothingWhenAlertStateUnchanged(): void
    {
        $this->ship->shouldReceive('getAlertState')
            ->withNoArgs()
            ->once()
            ->andReturn(ShipAlertStateEnum::ALERT_GREEN);

        $msg = $this->subject->changeAlertState($this->wrapper, ShipAlertStateEnum::ALERT_GREEN);

        $this->assertNull($msg);
    }

    public function testChangeAlertStateExpectNothingWhenChangedToGreen(): void
    {
        $this->ship->shouldReceive('getAlertState')
            ->withNoArgs()
            ->once()
            ->andReturn(ShipAlertStateEnum::ALERT_YELLOW);
        $this->ship->shouldReceive('setAlertState')
            ->with(ShipAlertStateEnum::ALERT_GREEN)
            ->once();

        $msg = $this->subject->changeAlertState($this->wrapper, ShipAlertStateEnum::ALERT_GREEN);

        $this->assertNull($msg);
    }

    public function testChangeAlertStateExpectErrorWhenNotEnoughEnergyForYellow(): void
    {
        static::expectException(InsufficientEnergyException::class);

        $this->ship->shouldReceive('getAlertState')
            ->withNoArgs()
            ->once()
            ->andReturn(ShipAlertStateEnum::ALERT_GREEN);

        $this->wrapper->shouldReceive('getEpsSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $this->subject->changeAlertState($this->wrapper, ShipAlertStateEnum::ALERT_YELLOW);
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
            ->andReturn(ShipAlertStateEnum::ALERT_GREEN);
        $this->ship->shouldReceive('setAlertState')
            ->with(ShipAlertStateEnum::ALERT_YELLOW)
            ->once();

        $this->wrapper->shouldReceive('getEpsSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($epsSystemData);

        $this->cancelRepair->shouldReceive('cancelRepair')
            ->with($this->ship)
            ->once()
            ->andReturn(false);

        $msg = $this->subject->changeAlertState($this->wrapper, ShipAlertStateEnum::ALERT_YELLOW);

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
            ->andReturn(ShipAlertStateEnum::ALERT_GREEN);

        $this->wrapper->shouldReceive('getEpsSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($epsSystemData);

        $this->subject->changeAlertState($this->wrapper, ShipAlertStateEnum::ALERT_RED);
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
            ->andReturn(ShipAlertStateEnum::ALERT_GREEN);
        $this->ship->shouldReceive('setAlertState')
            ->with(ShipAlertStateEnum::ALERT_RED)
            ->once();

        $this->wrapper->shouldReceive('getEpsSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($epsSystemData);

        $this->cancelRepair->shouldReceive('cancelRepair')
            ->with($this->ship)
            ->once()
            ->andReturn(true);

        $msg = $this->subject->changeAlertState($this->wrapper, ShipAlertStateEnum::ALERT_RED);

        $this->assertEquals('Die Reparatur wurde abgebrochen', $msg);
    }
}
