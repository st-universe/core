<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Orm\Entity\ShipInterface;
use Stu\StuTestCase;

class NearFieldScannerShipSystemTest extends StuTestCase
{

    /**
     * @var null|LongRangeScannerShipSystem
     */
    private $system;

    public function setUp(): void
    {
        $this->system = new NearFieldScannerShipSystem();
    }

    public function testCheckActivationConditionsReturnsFalseIfAlreadyActive(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('getNbs')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        $this->assertFalse(
            $this->system->checkActivationConditions($ship)
        );
    }

    public function testCheckActivationConditionsReturnsTrueIfActivateable(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('getNbs')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();

        $this->assertTrue(
            $this->system->checkActivationConditions($ship)
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
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('setNbs')
            ->with(true)
            ->once();

        $this->system->activate($ship);
    }

    public function testDeactivateDeactivates(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('setNbs')
            ->with(false)
            ->once();

        $this->system->deactivate($ship);
    }
}
