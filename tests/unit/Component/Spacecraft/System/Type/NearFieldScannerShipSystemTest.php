<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Type;

use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Component\Spacecraft\SpacecraftAlertStateEnum;
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
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\SpacecraftSystemInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\SpacecraftSystemRepositoryInterface;
use Stu\StuTestCase;

class NearFieldScannerShipSystemTest extends StuTestCase
{
    /** @var AstroEntryLibInterface&MockInterface */
    private $astroEntryLib;
    /** @var TrackerDeviceManagerInterface|MockInterface */
    private $trackerDeviceManager;

    /** @var ShipInterface&MockInterface */
    private $ship;
    /** @var ShipWrapperInterface&MockInterface */
    private $wrapper;

    private NearFieldScannerShipSystem $system;

    #[Override]
    public function setUp(): void
    {
        $this->ship = $this->mock(ShipInterface::class);
        $this->wrapper = $this->mock(ShipWrapperInterface::class);

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn($this->ship);

        $this->astroEntryLib = Mockery::mock(AstroEntryLibInterface::class);
        $this->trackerDeviceManager = Mockery::mock(TrackerDeviceManagerInterface::class);

        $this->system = new NearFieldScannerShipSystem(
            $this->astroEntryLib,
            $this->trackerDeviceManager
        );
    }

    public static function provideCheckActivationConditionsReturnsFalseIfNoColonyData(): array
    {
        return [
            [false, false, false, 'noch keine Kolonie kolonisiert wurde'],
            [false, true, true, null],
            [true, false, true, null],
        ];
    }

    #[DataProvider('provideCheckActivationConditionsReturnsFalseIfNoColonyData')]
    public function testCheckActivationConditions(bool $hasColony, bool $isNpc, bool $expectedResult, ?string $expectedReason): void
    {
        $this->ship->shouldReceive('getUser->hasColony')
            ->withNoArgs()
            ->andReturn($hasColony);
        $this->ship->shouldReceive('getUser->isNpc')
            ->withNoArgs()
            ->andReturn($isNpc);

        $reason = '';
        $result = $this->system->checkActivationConditions($this->wrapper, $reason);

        $this->assertEquals($expectedResult, $result);
        $this->assertEquals($expectedReason, $reason);
    }

    public function testCheckDeactivationConditionsReturnsFalseIfAlertRed(): void
    {
        $this->wrapper->shouldReceive('getAlertState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftAlertStateEnum::ALERT_RED);

        $reason = '';
        $this->assertFalse(
            $this->system->checkDeactivationConditions($this->wrapper, $reason)
        );
        $this->assertEquals('die Alarmstufe Rot ist', $reason);
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

        $this->wrapper->shouldReceive('getAlertState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftAlertStateEnum::ALERT_YELLOW);
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

    public function testCheckDeactivationConditionsReturnsTrueIfDeactivatable(): void
    {
        $this->wrapper->shouldReceive('getAlertState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftAlertStateEnum::ALERT_YELLOW);
        $this->wrapper->shouldReceive('getTrackerSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $reason = '';
        $this->assertTrue(
            $this->system->checkDeactivationConditions($this->wrapper, $reason)
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

    public function testActivateActivates(): void
    {
        $managerMock = $this->mock(SpacecraftSystemManagerInterface::class);
        $system = $this->mock(SpacecraftSystemInterface::class);

        $this->ship->shouldReceive('getSpacecraftSystem')
            ->with(SpacecraftSystemTypeEnum::NBS)
            ->once()
            ->andReturn($system);
        $system->shouldReceive('setMode')
            ->with(SpacecraftSystemModeEnum::MODE_ON)
            ->once();

        $this->system->activate($this->wrapper, $managerMock);
    }

    public function testDeactivateDeactivates(): void
    {
        $systemNbs = $this->mock(SpacecraftSystemInterface::class);
        $systemAstro = $this->mock(SpacecraftSystemInterface::class);

        $this->ship->shouldReceive('getSpacecraftSystem')
            ->with(SpacecraftSystemTypeEnum::NBS)
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

        $this->system->deactivate($this->wrapper);
    }

    public function testHandleDestruction(): void
    {
        $systemAstro = $this->mock(SpacecraftSystemInterface::class);

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

        $this->system->handleDestruction($this->wrapper);
    }
}
