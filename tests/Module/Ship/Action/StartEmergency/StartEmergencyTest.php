<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\StartEmergency;

use Override;
use Mockery;
use Mockery\MockInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\SpacecraftEmergencyInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\SpacecraftEmergencyRepositoryInterface;
use Stu\StuTestCase;

class StartEmergencyTest extends StuTestCase
{
    /** @var MockInterface&ShipLoaderInterface */
    private MockInterface $shipLoader;

    /** @var MockInterface&SpacecraftEmergencyRepositoryInterface */
    private MockInterface $spacecraftEmergencyRepository;

    /** @var MockInterface&StartEmergencyRequestInterface */
    private MockInterface $startEmergencyRequest;

    private StartEmergency $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->shipLoader = $this->mock(ShipLoaderInterface::class);
        $this->spacecraftEmergencyRepository = $this->mock(SpacecraftEmergencyRepositoryInterface::class);
        $this->startEmergencyRequest = $this->mock(StartEmergencyRequestInterface::class);

        $this->subject = new StartEmergency(
            $this->shipLoader,
            $this->spacecraftEmergencyRepository,
            $this->startEmergencyRequest
        );
    }

    public function testHandleDoesNothingIfEmergencyCallAlreadyActive(): void
    {
        $game = $this->mock(GameControllerInterface::class);
        $user = $this->mock(UserInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $shipWrapper = $this->mock(ShipWrapperInterface::class);

        $shipId = 666;
        $userId = 42;

        $game->shouldReceive('setView')
            ->with(ShowShip::VIEW_IDENTIFIER)
            ->once();
        $game->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);

        $this->startEmergencyRequest->shouldReceive('getShipId')
            ->withNoArgs()
            ->once()
            ->andReturn($shipId);

        $this->shipLoader->shouldReceive('getWrapperByIdAndUser')
            ->with($shipId, $userId)
            ->once()
            ->andReturn($shipWrapper);

        $shipWrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($ship);

        $ship->shouldReceive('getIsInEmergency')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        $this->subject->handle($game);
    }

    public function testHandleErrorsIfMessageIsTooLong(): void
    {
        $game = $this->mock(GameControllerInterface::class);
        $user = $this->mock(UserInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $shipWrapper = $this->mock(ShipWrapperInterface::class);

        $shipId = 666;
        $userId = 42;
        $text = str_repeat('ö', 251);

        $game->shouldReceive('setView')
            ->with(ShowShip::VIEW_IDENTIFIER)
            ->once();
        $game->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);
        $game->shouldReceive('addInformationf')
            ->with(
                'Maximal %d Zeichen erlaubt',
                250
            )
            ->once();

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);

        $this->startEmergencyRequest->shouldReceive('getShipId')
            ->withNoArgs()
            ->once()
            ->andReturn($shipId);
        $this->startEmergencyRequest->shouldReceive('getEmergencyText')
            ->withNoArgs()
            ->once()
            ->andReturn($text);

        $this->shipLoader->shouldReceive('getWrapperByIdAndUser')
            ->with($shipId, $userId)
            ->once()
            ->andReturn($shipWrapper);

        $shipWrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($ship);

        $ship->shouldReceive('getIsInEmergency')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();

        $this->subject->handle($game);
    }

    public function testHandleCreatesEmergencyCall(): void
    {
        $game = $this->mock(GameControllerInterface::class);
        $user = $this->mock(UserInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $shipWrapper = $this->mock(ShipWrapperInterface::class);
        $emergency = $this->mock(SpacecraftEmergencyInterface::class);

        $shipId = 666;
        $userId = 42;
        $text = str_repeat('ö', 250);

        $game->shouldReceive('setView')
            ->with(ShowShip::VIEW_IDENTIFIER)
            ->once();
        $game->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);
        $game->shouldReceive('addInformation')
            ->with('Das Notrufsignal wurde gestartet')
            ->once();

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);

        $this->startEmergencyRequest->shouldReceive('getShipId')
            ->withNoArgs()
            ->once()
            ->andReturn($shipId);
        $this->startEmergencyRequest->shouldReceive('getEmergencyText')
            ->withNoArgs()
            ->once()
            ->andReturn($text);

        $this->shipLoader->shouldReceive('getWrapperByIdAndUser')
            ->with($shipId, $userId)
            ->once()
            ->andReturn($shipWrapper);

        $shipWrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($ship);

        $ship->shouldReceive('getIsInEmergency')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();

        $this->spacecraftEmergencyRepository->shouldReceive('prototype')
            ->withNoArgs()
            ->once()
            ->andReturn($emergency);
        $this->spacecraftEmergencyRepository->shouldReceive('save')
            ->with($emergency)
            ->once();

        $emergency->shouldReceive('setShip')
            ->with($ship)
            ->once();
        $emergency->shouldReceive('setText')
            ->with($text)
            ->once();
        $emergency->shouldReceive('setDate')
            ->with(Mockery::type('int'))
            ->once();

        $ship->shouldReceive('setIsInEmergency')
            ->with(true)
            ->once();

        $this->subject->handle($game);
    }

    public function testPerformSessionCheckReturnsTrue(): void
    {
        $this->assertTrue(
            $this->subject->performSessionCheck()
        );
    }
}
