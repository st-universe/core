<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Mockery\MockInterface;
use Stu\Component\Ship\System\Data\AbstractReactorSystemData;
use Stu\Component\Ship\System\Data\EpsSystemData;
use Stu\Component\Ship\System\Data\WarpDriveSystemData;
use Stu\StuTestCase;

class ReactorWrapperTest extends StuTestCase
{
    /** @var MockInterface|ShipWrapperInterface */
    private MockInterface $wrapper;

    /** @var MockInterface|AbstractReactorSystemData */
    private MockInterface $reactorSystemData;

    private ReactorWrapperInterface $subject;

    public function setUp(): void
    {
        $this->wrapper = $this->mock(ShipWrapperInterface::class);
        $this->reactorSystemData = $this->mock(AbstractReactorSystemData::class);

        $this->subject = new ReactorWrapper(
            $this->wrapper,
            $this->reactorSystemData
        );
    }

    public function testGetExpectWrapperFromCtor(): void
    {
        $this->assertEquals($this->reactorSystemData, $this->subject->get());
    }

    public function testGetEpsProductionExpectEpsUsageOnlyWhenSplitIsZero(): void
    {
        $warpdrive = $this->mock(WarpDriveSystemData::class);

        $this->wrapper->shouldReceive('getWarpDriveSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($warpdrive);
        $this->wrapper->shouldReceive('getEpsUsage')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->reactorSystemData->shouldReceive('getOutput')
            ->withNoArgs()
            ->once()
            ->andReturn(100);
        $this->reactorSystemData->shouldReceive('getLoad')
            ->withNoArgs()
            ->andReturn(90);

        $warpdrive->shouldReceive('getWarpDriveSplit')
            ->withNoArgs()
            ->once()
            ->andReturn(0);

        $result = $this->subject->getEpsProduction();
        $result = $this->subject->getEpsProduction();

        $this->assertEquals(42, $result);
    }

    public function testGetEpsProductionExpectCappedByReactorLoading(): void
    {
        $warpdrive = $this->mock(WarpDriveSystemData::class);

        $this->wrapper->shouldReceive('getWarpDriveSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($warpdrive);
        $this->wrapper->shouldReceive('getEpsUsage')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->reactorSystemData->shouldReceive('getOutput')
            ->withNoArgs()
            ->once()
            ->andReturn(100);
        $this->reactorSystemData->shouldReceive('getLoad')
            ->withNoArgs()
            ->andReturn(21);

        $warpdrive->shouldReceive('getWarpDriveSplit')
            ->withNoArgs()
            ->once()
            ->andReturn(0);

        $result = $this->subject->getEpsProduction();
        $result = $this->subject->getEpsProduction();

        $this->assertEquals(21, $result);
    }

    public function testGetEpsProductionWhenNoWarpdriveInstalled(): void
    {
        $this->wrapper->shouldReceive('getWarpDriveSystemData')
            ->withNoArgs()
            ->twice()
            ->andReturn(null);
        $this->wrapper->shouldReceive('get->getRump->getFlightEcost')
            ->withNoArgs()
            ->once()
            ->andReturn(99999);

        $this->reactorSystemData->shouldReceive('getOutput')
            ->withNoArgs()
            ->once()
            ->andReturn(100);
        $this->reactorSystemData->shouldReceive('getLoad')
            ->withNoArgs()
            ->andReturn(90);

        $result = $this->subject->getEpsProduction();
        $result = $this->subject->getEpsProduction();

        $this->assertEquals(90, $result);
    }

    public function testGetEpsProductionWhenWarpdriveInstalled(): void
    {
        $warpdrive = $this->mock(WarpDriveSystemData::class);

        $this->wrapper->shouldReceive('getWarpDriveSystemData')
            ->withNoArgs()
            ->twice()
            ->andReturn($warpdrive);
        $this->wrapper->shouldReceive('get->getRump->getFlightEcost')
            ->withNoArgs()
            ->twice()
            ->andReturn(5);
        $this->wrapper->shouldReceive('getEpsUsage')
            ->withNoArgs()
            ->once()
            ->andReturn(10);

        $warpdrive->shouldReceive('getWarpDriveSplit')
            ->withNoArgs()
            ->twice()
            ->andReturn(50);

        $this->reactorSystemData->shouldReceive('getOutput')
            ->withNoArgs()
            ->andReturn(110);
        $this->reactorSystemData->shouldReceive('getLoad')
            ->withNoArgs()
            ->andReturn(110);

        $result = $this->subject->getEpsProduction();
        $result = $this->subject->getEpsProduction();

        $this->assertEquals(60, $result);
    }

    public function testGetEffectiveEpsProduction(): void
    {
        $warpdrive = $this->mock(WarpDriveSystemData::class);
        $eps = $this->mock(EpsSystemData::class);

        $this->wrapper->shouldReceive('getWarpDriveSystemData')
            ->withNoArgs()
            ->twice()
            ->andReturn($warpdrive);
        $this->wrapper->shouldReceive('getEpsSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($eps);
        $this->wrapper->shouldReceive('get->getRump->getFlightEcost')
            ->withNoArgs()
            ->twice()
            ->andReturn(5);
        $this->wrapper->shouldReceive('getEpsUsage')
            ->withNoArgs()
            ->andReturn(10);

        $warpdrive->shouldReceive('getWarpDriveSplit')
            ->withNoArgs()
            ->twice()
            ->andReturn(50);

        $eps->shouldReceive('getEps')
            ->withNoArgs()
            ->once()
            ->andReturn(50);
        $eps->shouldReceive('getMaxEps')
            ->withNoArgs()
            ->once()
            ->andReturn(77);

        $this->reactorSystemData->shouldReceive('getOutput')
            ->withNoArgs()
            ->andReturn(110);
        $this->reactorSystemData->shouldReceive('getLoad')
            ->withNoArgs()
            ->andReturn(110);

        $result = $this->subject->getEffectiveEpsProduction();
        $result = $this->subject->getEffectiveEpsProduction();

        $this->assertEquals(27, $result);
    }

    public function testGetEffectiveEpsProductionExpectNegativeWhenReactorNotFullEnough(): void
    {
        $warpdrive = $this->mock(WarpDriveSystemData::class);
        $eps = $this->mock(EpsSystemData::class);

        $this->wrapper->shouldReceive('getWarpDriveSystemData')
            ->withNoArgs()
            ->twice()
            ->andReturn($warpdrive);
        $this->wrapper->shouldReceive('getEpsSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($eps);
        $this->wrapper->shouldReceive('get->getRump->getFlightEcost')
            ->withNoArgs()
            ->twice()
            ->andReturn(5);
        $this->wrapper->shouldReceive('getEpsUsage')
            ->withNoArgs()
            ->andReturn(10);

        $warpdrive->shouldReceive('getWarpDriveSplit')
            ->withNoArgs()
            ->twice()
            ->andReturn(50);

        $eps->shouldReceive('getEps')
            ->withNoArgs()
            ->once()
            ->andReturn(50);
        $eps->shouldReceive('getMaxEps')
            ->withNoArgs()
            ->once()
            ->andReturn(77);

        $this->reactorSystemData->shouldReceive('getOutput')
            ->withNoArgs()
            ->andReturn(110);
        $this->reactorSystemData->shouldReceive('getLoad')
            ->withNoArgs()
            ->andReturn(1);

        $result = $this->subject->getEffectiveEpsProduction();
        $result = $this->subject->getEffectiveEpsProduction();

        $this->assertEquals(-9, $result);
    }

    public function testGetEffectiveWarpDriveProduction(): void
    {
        $warpdrive = $this->mock(WarpDriveSystemData::class);

        $this->wrapper->shouldReceive('getWarpDriveSystemData')
            ->withNoArgs()
            ->twice()
            ->andReturn($warpdrive);
        $this->wrapper->shouldReceive('get->getRump->getFlightEcost')
            ->withNoArgs()
            ->once()
            ->andReturn(4);
        $this->wrapper->shouldReceive('getEpsUsage')
            ->withNoArgs()
            ->andReturn(10);

        $warpdrive->shouldReceive('getWarpDriveSplit')
            ->withNoArgs()
            ->andReturn(50);
        $warpdrive->shouldReceive('getMaxWarpDrive')
            ->withNoArgs()
            ->andReturn(42);
        $warpdrive->shouldReceive('getWarpDrive')
            ->withNoArgs()
            ->once()
            ->andReturn(30);

        $this->reactorSystemData->shouldReceive('getOutput')
            ->withNoArgs()
            ->andReturn(110);
        $this->reactorSystemData->shouldReceive('getLoad')
            ->withNoArgs()
            ->andReturn(110);

        $result = $this->subject->getEffectiveWarpDriveProduction();
        $result = $this->subject->getEffectiveWarpDriveProduction();

        $this->assertEquals(12, $result);
    }
}
