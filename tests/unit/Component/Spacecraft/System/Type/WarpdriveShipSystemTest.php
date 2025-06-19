<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Type;

use Mockery;
use Mockery\MockInterface;
use Override;
use Stu\Component\Spacecraft\Event\WarpdriveActivationEvent;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\ReactorWrapperInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\SpacecraftSystemInterface;
use Stu\Orm\Entity\StarSystemInterface;
use Stu\Orm\Entity\TholianWebInterface;
use Stu\StuTestCase;

//TODO@hux test handleDamage + handleDestruction
class WarpdriveShipSystemTest extends StuTestCase
{
    private MockInterface&GameControllerInterface $game;

    private MockInterface&SpacecraftSystemManagerInterface $managerMock;

    private WarpdriveShipSystem $system;

    private MockInterface&ShipInterface $ship;
    private MockInterface&ShipWrapperInterface $wrapper;

    #[Override]
    public function setUp(): void
    {
        $this->game = $this->mock(GameControllerInterface::class);

        $this->managerMock = $this->mock(SpacecraftSystemManagerInterface::class);

        $this->ship = $this->mock(ShipInterface::class);
        $this->wrapper = $this->mock(ShipWrapperInterface::class);

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn($this->ship);

        $this->system = new WarpdriveShipSystem(
            $this->game
        );
    }

    public function testCheckActivationConditionsReturnsFalseIfShipIsTractored(): void
    {
        $this->ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $reason = '';
        $this->assertFalse(
            $this->system->checkActivationConditions($this->wrapper, $reason)
        );

        $this->assertEquals('es von einem Traktorstrahl gehalten wird', $reason);
    }

    public function testCheckActivationConditionsReturnsFalseIfShipInActiveTholianWeb(): void
    {
        $tholianWeb = $this->mock(TholianWebInterface::class);

        $this->ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('getHoldingWeb')
            ->withNoArgs()
            ->twice()
            ->andReturn($tholianWeb);
        $tholianWeb->shouldReceive('isFinished')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $reason = '';
        $this->assertFalse(
            $this->system->checkActivationConditions($this->wrapper, $reason)
        );

        $this->assertEquals('es in einem Energienetz gefangen ist', $reason);
    }

    public function testCheckActivationConditionsReturnsFalseIfShipInWormhole(): void
    {
        $this->ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('getHoldingWeb')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $starSystem = Mockery::mock(StarSystemInterface::class);
        $this->ship->shouldReceive('getSystem')
            ->withNoArgs()
            ->twice()
            ->andReturn($starSystem);

        $starSystem->shouldReceive('isWormhole')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $reason = '';
        $this->assertFalse(
            $this->system->checkActivationConditions($this->wrapper, $reason)
        );

        $this->assertEquals('es sich in einem Wurmloch befindet', $reason);
    }

    public function testCheckActivationConditionsReturnsFalseIfWarpcoreDestroyed(): void
    {
        $reactorWrapper = $this->mock(ReactorWrapperInterface::class);

        $this->ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('getHoldingWeb')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $starSystem = Mockery::mock(StarSystemInterface::class);
        $this->ship->shouldReceive('getSystem')
            ->withNoArgs()
            ->twice()
            ->andReturn($starSystem);

        $starSystem->shouldReceive('isWormhole')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->wrapper->shouldReceive('getReactorWrapper')
            ->withNoArgs()
            ->once()
            ->andReturn($reactorWrapper);
        $reactorWrapper->shouldReceive('isHealthy')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $reactorWrapper->shouldReceive('get->getSystemType')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftSystemTypeEnum::WARPCORE);

        $reason = '';
        $this->assertFalse(
            $this->system->checkActivationConditions($this->wrapper, $reason)
        );

        $this->assertEquals('der Warpkern zerstört ist', $reason);
    }

    public function testCheckActivationConditionsReturnsTrueIfActivateable(): void
    {
        $reactorWrapper = $this->mock(ReactorWrapperInterface::class);

        $this->ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('getHoldingWeb')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $starSystem = Mockery::mock(StarSystemInterface::class);
        $this->ship->shouldReceive('getSystem')
            ->withNoArgs()
            ->twice()
            ->andReturn($starSystem);

        $starSystem->shouldReceive('isWormhole')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->wrapper->shouldReceive('getReactorWrapper')
            ->withNoArgs()
            ->once()
            ->andReturn($reactorWrapper);
        $reactorWrapper->shouldReceive('isHealthy')
            ->withNoArgs()
            ->once()
            ->andReturn(true);


        $reason = '';
        $this->assertTrue(
            $this->system->checkActivationConditions($this->wrapper, $reason)
        );

        $this->assertEmpty($reason);
    }

    public function testGetEnergyUsageForActivationReturnsValue(): void
    {
        $this->assertSame(
            1,
            $this->system->getEnergyUsageForActivation()
        );
    }

    public function testActivateActivatesAndActivatesWarpStateOnTraktorShip(): void
    {
        $system = $this->mock(SpacecraftSystemInterface::class);

        //DOCKING STUFF
        $this->ship->shouldReceive('setDockedTo')
            ->with(null)
            ->once();

        //SYSTEM ACTIVATION
        $this->ship->shouldReceive('getSpacecraftSystem')
            ->with(SpacecraftSystemTypeEnum::WARPDRIVE)
            ->once()
            ->andReturn($system);
        $system->shouldReceive('setMode')
            ->with(SpacecraftSystemModeEnum::MODE_ON)
            ->once();

        /** @var WarpdriveActivationEvent|null */
        $event = null;
        $this->game->shouldReceive('triggerEvent')
            ->with(Mockery::on(function ($arg) use (&$event): bool {
                $event = $arg;
                return true;
            }))
            ->once();

        $this->system->activate($this->wrapper, $this->managerMock);

        $this->assertNotNull($event);
        $this->assertEquals($this->wrapper, $event->getWrapper());
    }

    public function testDeactivateDeactivates(): void
    {
        $system = $this->mock(SpacecraftSystemInterface::class);

        $this->ship->shouldReceive('getSpacecraftSystem')
            ->with(SpacecraftSystemTypeEnum::WARPDRIVE)
            ->once()
            ->andReturn($system);
        $system->shouldReceive('setMode')
            ->with(SpacecraftSystemModeEnum::MODE_OFF)
            ->once();

        $this->system->deactivate($this->wrapper);
    }
}
