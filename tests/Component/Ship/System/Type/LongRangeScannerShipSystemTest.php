<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Mockery;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Ship\Lib\AstroEntryLibInterface;
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

    public function setUp(): void
    {
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
        $ship = $this->mock(ShipInterface::class);
        $managerMock = $this->mock(ShipSystemManagerInterface::class);
        $system = $this->mock(ShipSystemInterface::class);

        $ship->shouldReceive('getShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_LSS)
            ->once()
            ->andReturn($system);
        $system->shouldReceive('setMode')
            ->with(ShipSystemModeEnum::MODE_ON)
            ->once();

        $this->system->activate($ship, $managerMock);
    }

    public function testDeactivateDeactivates(): void
    {
        $ship = $this->mock(ShipInterface::class);
        $systemNbs = $this->mock(ShipSystemInterface::class);
        $systemAstro = $this->mock(ShipSystemInterface::class);

        $ship->shouldReceive('getShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_LSS)
            ->once()
            ->andReturn($systemNbs);
        $systemNbs->shouldReceive('setMode')
            ->with(ShipSystemModeEnum::MODE_OFF)
            ->once();

        //ASTRO STUFF
        $ship->shouldReceive('hasShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_ASTRO_LABORATORY)
            ->once()
            ->andReturnTrue();
        $ship->shouldReceive('getShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_ASTRO_LABORATORY)
            ->once()
            ->andReturn($systemAstro);
        $systemAstro->shouldReceive('setMode')
            ->with(ShipSystemModeEnum::MODE_OFF)
            ->once();
        $ship->shouldReceive('getState')
            ->with()
            ->once()
            ->andReturn(ShipStateEnum::SHIP_STATE_SYSTEM_MAPPING);
        $this->astroEntryLib->shouldReceive('cancelAstroFinalizing')
            ->with($ship)
            ->once();

        $this->system->deactivate($ship);
    }

    public function testHandleDestruction(): void
    {
        $ship = $this->mock(ShipInterface::class);
        $systemAstro = $this->mock(ShipSystemInterface::class);

        //ASTRO STUFF
        $ship->shouldReceive('hasShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_ASTRO_LABORATORY)
            ->once()
            ->andReturnTrue();
        $ship->shouldReceive('getShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_ASTRO_LABORATORY)
            ->once()
            ->andReturn($systemAstro);
        $systemAstro->shouldReceive('setMode')
            ->with(ShipSystemModeEnum::MODE_OFF)
            ->once();
        $ship->shouldReceive('getState')
            ->with()
            ->once()
            ->andReturn(ShipStateEnum::SHIP_STATE_SYSTEM_MAPPING);
        $this->astroEntryLib->shouldReceive('cancelAstroFinalizing')
            ->with($ship)
            ->once();

        $this->system->handleDestruction($ship);
    }
}
