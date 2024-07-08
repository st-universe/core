<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Component\Ship\ShipAlertStateEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\Data\TrackerSystemData;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Ship\Lib\AstroEntryLibInterface;
use Stu\Module\Ship\Lib\Interaction\TrackerDeviceManagerInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipSystemInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\ShipSystemRepositoryInterface;
use Stu\StuTestCase;

class NearFieldScannerShipSystemTest extends StuTestCase
{
    private NearFieldScannerShipSystem $system;

    /**
     * @var AstroEntryLibInterface|MockInterface
     */
    private $astroEntryLib;

    /**
     * @var TrackerDeviceManagerInterface|MockInterface
     */
    private $trackerDeviceManager;

    private ShipInterface $ship;
    private ShipWrapperInterface $wrapper;

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
        $this->ship->shouldReceive('getAlertState')
            ->withNoArgs()
            ->once()
            ->andReturn(ShipAlertStateEnum::ALERT_RED);

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
            $this->mock(ShipWrapperFactoryInterface::class),
            $this->mock(ShipSystemRepositoryInterface::class)
        );
        $trackerSystemData->setTarget(42);

        $this->ship->shouldReceive('getAlertState')
            ->withNoArgs()
            ->once()
            ->andReturn(ShipAlertStateEnum::ALERT_YELLOW);
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
        $this->ship->shouldReceive('getAlertState')
            ->withNoArgs()
            ->once()
            ->andReturn(ShipAlertStateEnum::ALERT_YELLOW);
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
        $managerMock = $this->mock(ShipSystemManagerInterface::class);
        $system = $this->mock(ShipSystemInterface::class);

        $this->ship->shouldReceive('getShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_NBS)
            ->once()
            ->andReturn($system);
        $system->shouldReceive('setMode')
            ->with(ShipSystemModeEnum::MODE_ON)
            ->once();

        $this->system->activate($this->wrapper, $managerMock);
    }

    public function testDeactivateDeactivates(): void
    {
        $systemNbs = $this->mock(ShipSystemInterface::class);
        $systemAstro = $this->mock(ShipSystemInterface::class);

        $this->ship->shouldReceive('getShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_NBS)
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
            ->andReturn(ShipStateEnum::SHIP_STATE_ASTRO_FINALIZING);
        $this->astroEntryLib->shouldReceive('cancelAstroFinalizing')
            ->with($this->wrapper)
            ->once();

        $this->system->deactivate($this->wrapper);
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
            ->andReturn(ShipStateEnum::SHIP_STATE_ASTRO_FINALIZING);
        $this->astroEntryLib->shouldReceive('cancelAstroFinalizing')
            ->with($this->wrapper)
            ->once();

        $this->trackerDeviceManager->shouldReceive('deactivateTrackerIfActive')
            ->with($this->wrapper, false)
            ->once();

        $this->system->handleDestruction($this->wrapper);
    }
}
