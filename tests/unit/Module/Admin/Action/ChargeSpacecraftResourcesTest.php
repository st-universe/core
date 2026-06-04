<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action;

use Mockery\MockInterface;
use request;
use Stu\Component\Spacecraft\System\Data\EpsSystemData;
use Stu\Module\Admin\View\Scripts\ShowScripts;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Stu\Orm\Repository\StationRepositoryInterface;
use Stu\StuTestCase;

class ChargeSpacecraftResourcesTest extends StuTestCase
{
    private MockInterface&SpacecraftRepositoryInterface $spacecraftRepository;
    private MockInterface&ShipRepositoryInterface $shipRepository;
    private MockInterface&StationRepositoryInterface $stationRepository;
    private MockInterface&SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory;
    private MockInterface&GameControllerInterface $game;

    private ChargeSpacecraftResources $subject;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->spacecraftRepository = $this->mock(SpacecraftRepositoryInterface::class);
        $this->shipRepository = $this->mock(ShipRepositoryInterface::class);
        $this->stationRepository = $this->mock(StationRepositoryInterface::class);
        $this->spacecraftWrapperFactory = $this->mock(SpacecraftWrapperFactoryInterface::class);
        $this->game = $this->mock(GameControllerInterface::class);

        $this->subject = new ChargeSpacecraftResources(
            $this->spacecraftRepository,
            $this->shipRepository,
            $this->stationRepository,
            $this->spacecraftWrapperFactory
        );
    }

    public function testHandleDoesNothingWhenNotAdmin(): void
    {
        $this->game->shouldReceive('setView')
            ->with(ShowScripts::VIEW_IDENTIFIER)
            ->once();
        $this->game->shouldReceive('isAdmin')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->game->shouldReceive('getInfo->addInformation')
            ->with('[b][color=#ff2626]Aktion nicht möglich, Spieler ist kein Admin![/color][/b]')
            ->once();

        $this->subject->handle($this->game);
    }

    public function testHandleRequiresConfirmationForGlobalCharge(): void
    {
        request::setMockVars([
            'spacecraft_charge_target' => 'eps',
            'spacecraft_charge_value' => '100',
            'spacecraft_charge_ships' => '1',
            'spacecraft_charge_confirmed' => '0'
        ]);

        $this->game->shouldReceive('setView')
            ->with(ShowScripts::VIEW_IDENTIFIER)
            ->once();
        $this->game->shouldReceive('isAdmin')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $this->game->shouldReceive('getInfo->addInformation')
            ->with('Bitte die globale Spacecraft-Ladung zuerst bestätigen')
            ->once();

        $this->subject->handle($this->game);
    }

    public function testHandleCapsExplicitSpacecraftEpsWithoutOverride(): void
    {
        request::setMockVars([
            'spacecraft_charge_target' => 'eps',
            'spacecraft_charge_value' => '100',
            'spacecraft_charge_spacecraft_ids' => '42'
        ]);

        $spacecraft = $this->mock(Spacecraft::class);
        $wrapper = $this->mock(SpacecraftWrapperInterface::class);
        $epsSystemData = $this->mock(EpsSystemData::class);

        $this->game->shouldReceive('setView')
            ->with(ShowScripts::VIEW_IDENTIFIER)
            ->once();
        $this->game->shouldReceive('isAdmin')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $this->spacecraftRepository->shouldReceive('find')
            ->with(42)
            ->once()
            ->andReturn($spacecraft);
        $spacecraft->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);
        $this->spacecraftWrapperFactory->shouldReceive('wrapSpacecraft')
            ->with($spacecraft)
            ->once()
            ->andReturn($wrapper);
        $wrapper->shouldReceive('getEpsSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($epsSystemData);
        $epsSystemData->shouldReceive('getMaxEps')
            ->withNoArgs()
            ->once()
            ->andReturn(50);
        $epsSystemData->shouldReceive('setEps')
            ->with(50)
            ->once()
            ->andReturnSelf();
        $epsSystemData->shouldReceive('update')
            ->withNoArgs()
            ->once();
        $this->game->shouldReceive('getInfo->addInformation')
            ->with('1 Spacecraft wurde für EPS mit Wert 100 verarbeitet (je Spacecraft auf das aktuelle Maximum begrenzt).')
            ->once();

        $this->subject->handle($this->game);
    }

    public function testPerformSessionCheckReturnsTrue(): void
    {
        static::assertTrue($this->subject->performSessionCheck());
    }
}
