<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Type;

use Mockery;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Component\Spacecraft\System\Data\TrackerSystemData;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Ship\Lib\AstroEntryLibInterface;
use Stu\Module\Spacecraft\Lib\Interaction\TrackerDeviceManagerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Template\StatusBarFactoryInterface;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\SpacecraftSystemRepositoryInterface;
use Stu\StuTestCase;

class LongRangeScannerShipSystemTest extends StuTestCase
{
    private LongRangeScannerShipSystem $system;

    /**
     * @var null|AstroEntryLibInterface|MockInterface
     */
    private $astroEntryLib;

    /**
     * @var TrackerDeviceManagerInterface|MockInterface
     */
    private $trackerDeviceManager;

    private Ship $ship;
    private ShipWrapperInterface $wrapper;

    #[\Override]
    public function setUp(): void
    {
        $this->ship = $this->mock(Ship::class);
        $this->wrapper = $this->mock(ShipWrapperInterface::class);
        $this->astroEntryLib = Mockery::mock(AstroEntryLibInterface::class);
        $this->trackerDeviceManager = Mockery::mock(TrackerDeviceManagerInterface::class);

        $this->system = new LongRangeScannerShipSystem(
            $this->astroEntryLib,
            $this->trackerDeviceManager
        );
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
        $managerMock = $this->mock(SpacecraftSystemManagerInterface::class);
        $system = $this->mock(SpacecraftSystem::class);

        $this->ship->shouldReceive('getSpacecraftSystem')
            ->with(SpacecraftSystemTypeEnum::LSS)
            ->once()
            ->andReturn($system);
        $system->shouldReceive('setMode')
            ->with(SpacecraftSystemModeEnum::MODE_ON)
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
        $systemNbs = $this->mock(SpacecraftSystem::class);
        $systemAstro = $this->mock(SpacecraftSystem::class);

        $this->ship->shouldReceive('getSpacecraftSystem')
            ->with(SpacecraftSystemTypeEnum::LSS)
            ->once()
            ->andReturn($systemNbs);
        $systemNbs->shouldReceive('setMode')
            ->with(SpacecraftSystemModeEnum::MODE_OFF)
            ->once();

        //ASTRO STUFF
        $this->ship->shouldReceive('hasSpacecraftSystem')
            ->with(SpacecraftSystemTypeEnum::ASTRO_LABORATORY)
            ->once()
            ->andReturnTrue();
        $this->ship->shouldReceive('getSpacecraftSystem')
            ->with(SpacecraftSystemTypeEnum::ASTRO_LABORATORY)
            ->once()
            ->andReturn($systemAstro);
        $systemAstro->shouldReceive('setMode')
            ->with(SpacecraftSystemModeEnum::MODE_OFF)
            ->once();
        $this->ship->shouldReceive('getState')
            ->with()
            ->once()
            ->andReturn(SpacecraftStateEnum::ASTRO_FINALIZING);
        $this->astroEntryLib->shouldReceive('cancelAstroFinalizing')
            ->with($this->wrapper)
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
        $trackerSystemData = new TrackerSystemData(
            $this->mock(ShipRepositoryInterface::class),
            $this->mock(SpacecraftWrapperFactoryInterface::class),
            $this->mock(SpacecraftSystemRepositoryInterface::class),
            $this->mock(StatusBarFactoryInterface::class)
        );
        $trackerSystemData->setTarget(42);

        //wrapper
        $this->wrapper->shouldReceive('getTrackerSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($trackerSystemData);

        $reason = '';
        $this->assertFalse(
            $this->system->checkDeactivationConditions($this->wrapper, $reason)
        );
        $this->assertEquals('der Tracker aktiv ist', $reason);
    }

    public function testHandleDestruction(): void
    {
        $systemAstro = $this->mock(SpacecraftSystem::class);
        $this->mock(SpacecraftSystem::class);

        //ASTRO STUFF
        $this->ship->shouldReceive('hasSpacecraftSystem')
            ->with(SpacecraftSystemTypeEnum::ASTRO_LABORATORY)
            ->once()
            ->andReturnTrue();
        $this->ship->shouldReceive('getSpacecraftSystem')
            ->with(SpacecraftSystemTypeEnum::ASTRO_LABORATORY)
            ->once()
            ->andReturn($systemAstro);
        $systemAstro->shouldReceive('setMode')
            ->with(SpacecraftSystemModeEnum::MODE_OFF)
            ->once();
        $this->ship->shouldReceive('getState')
            ->with()
            ->once()
            ->andReturn(SpacecraftStateEnum::ASTRO_FINALIZING);
        $this->astroEntryLib->shouldReceive('cancelAstroFinalizing')
            ->with($this->wrapper)
            ->once();

        $this->trackerDeviceManager->shouldReceive('deactivateTrackerIfActive')
            ->with($this->wrapper, false)
            ->once();

        //wrapper
        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);

        $this->system->handleDestruction($this->wrapper);
    }
}
