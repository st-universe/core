<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Mockery;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\Data\TrackerSystemData;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Ship\Lib\AstroEntryLibInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipSystemInterface;
use Stu\StuTestCase;

class LongRangeScannerShipSystemTest extends StuTestCase
{

    /**
     * @var null|LongRangeScannerShipSystem
     */
    private $system;

    /**
     * @var null|AstroEntryLibInterface|MockInterface
     */
    private $astroEntryLib;

    private ShipInterface $ship;
    private ShipWrapperInterface $wrapper;

    public function setUp(): void
    {
        $this->ship = $this->mock(ShipInterface::class);
        $this->wrapper = $this->mock(ShipWrapperInterface::class);
        $this->astroEntryLib = Mockery::mock(AstroEntryLibInterface::class);

        $this->system = new LongRangeScannerShipSystem($this->astroEntryLib);
    }

    public function testGetEnergyUsageForActivationReturnsValue(): void
    {
        $this->assertSame(
            1,
            $this->system->getEnergyUsageForActivation()
        );
    }

    public function testActivateActivates(): void
    {
        $managerMock = $this->mock(ShipSystemManagerInterface::class);
        $system = $this->mock(ShipSystemInterface::class);

        $this->ship->shouldReceive('getShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_LSS)
            ->once()
            ->andReturn($system);
        $system->shouldReceive('setMode')
            ->with(ShipSystemModeEnum::MODE_ON)
            ->once();
        //wrapper
        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);

        $this->system->activate($this->wrapper, $managerMock);
    }

    public function testDeactivateDeactivates(): void
    {
        $systemNbs = $this->mock(ShipSystemInterface::class);
        $systemAstro = $this->mock(ShipSystemInterface::class);

        $this->ship->shouldReceive('getShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_LSS)
            ->once()
            ->andReturn($systemNbs);
        $systemNbs->shouldReceive('setMode')
            ->with(ShipSystemModeEnum::MODE_OFF)
            ->once();

        //ASTRO STUFF
        $this->ship->shouldReceive('hasShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_ASTRO_LABORATORY)
            ->once()
            ->andReturnTrue();
        $this->ship->shouldReceive('getShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_ASTRO_LABORATORY)
            ->once()
            ->andReturn($systemAstro);
        $systemAstro->shouldReceive('setMode')
            ->with(ShipSystemModeEnum::MODE_OFF)
            ->once();
        $this->ship->shouldReceive('getState')
            ->with()
            ->once()
            ->andReturn(ShipStateEnum::SHIP_STATE_SYSTEM_MAPPING);
        $this->astroEntryLib->shouldReceive('cancelAstroFinalizing')
            ->with($this->ship)
            ->once();
        //wrapper
        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);

        $this->system->deactivate($this->wrapper);
    }

    public function testCheckDeactivationConditionsReturnsFalseIfTrackerActive(): void
    {
        $trackerSystemData = $this->mock(TrackerSystemData::class);
        $targetWrapper = $this->mock(ShipWrapperInterface::class);

        //wrapper
        $this->wrapper->shouldReceive('getTrackerSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($trackerSystemData);
        $trackerSystemData->shouldReceive('getTargetWrapper')
            ->withNoArgs()
            ->once()
            ->andReturn($targetWrapper);

        $reason = null;
        $this->assertFalse(
            $this->system->checkDeactivationConditions($this->wrapper, $reason)
        );
        $this->assertEquals('der Tracker aktiv ist', $reason);
    }

    public function testHandleDestruction(): void
    {
        $systemAstro = $this->mock(ShipSystemInterface::class);

        //ASTRO STUFF
        $this->ship->shouldReceive('hasShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_ASTRO_LABORATORY)
            ->once()
            ->andReturnTrue();
        $this->ship->shouldReceive('getShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_ASTRO_LABORATORY)
            ->once()
            ->andReturn($systemAstro);
        $systemAstro->shouldReceive('setMode')
            ->with(ShipSystemModeEnum::MODE_OFF)
            ->once();
        $this->ship->shouldReceive('getState')
            ->with()
            ->once()
            ->andReturn(ShipStateEnum::SHIP_STATE_SYSTEM_MAPPING);
        $this->astroEntryLib->shouldReceive('cancelAstroFinalizing')
            ->with($this->ship)
            ->once();
        //wrapper
        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);

        $this->system->handleDestruction($this->wrapper);
    }
}
