<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\StartEmergency;

use Mockery;
use Mockery\MockInterface;
use Override;
use Stu\Component\Spacecraft\System\Data\ComputerSystemData;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\SpacecraftEmergencyInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\SpacecraftEmergencyRepositoryInterface;
use Stu\StuTestCase;

class StartEmergencyTest extends StuTestCase
{
    /** @var MockInterface&SpacecraftLoaderInterface */
    private $spacecraftLoader;
    /** @var MockInterface&SpacecraftEmergencyRepositoryInterface */
    private $spacecraftEmergencyRepository;
    /** @var MockInterface&StartEmergencyRequestInterface */
    private $startEmergencyRequest;

    private StartEmergency $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->spacecraftLoader = $this->mock(SpacecraftLoaderInterface::class);
        $this->spacecraftEmergencyRepository = $this->mock(SpacecraftEmergencyRepositoryInterface::class);
        $this->startEmergencyRequest = $this->mock(StartEmergencyRequestInterface::class);

        $this->subject = new StartEmergency(
            $this->spacecraftLoader,
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
            ->with(ShowSpacecraft::VIEW_IDENTIFIER)
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

        $this->spacecraftLoader->shouldReceive('getWrapperByIdAndUser')
            ->with($shipId, $userId)
            ->once()
            ->andReturn($shipWrapper);

        $shipWrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($ship);

        $shipWrapper->shouldReceive('getComputerSystemDataMandatory->isInEmergency')
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
            ->with(ShowSpacecraft::VIEW_IDENTIFIER)
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

        $this->spacecraftLoader->shouldReceive('getWrapperByIdAndUser')
            ->with($shipId, $userId)
            ->once()
            ->andReturn($shipWrapper);

        $shipWrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($ship);

        $shipWrapper->shouldReceive('getComputerSystemDataMandatory->isInEmergency')
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
        $computer = $this->mock(ComputerSystemData::class);

        $shipId = 666;
        $userId = 42;
        $text = str_repeat('ö', 250);

        $game->shouldReceive('setView')
            ->with(ShowSpacecraft::VIEW_IDENTIFIER)
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

        $this->spacecraftLoader->shouldReceive('getWrapperByIdAndUser')
            ->with($shipId, $userId)
            ->once()
            ->andReturn($shipWrapper);

        $shipWrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($ship);

        $shipWrapper->shouldReceive('getComputerSystemDataMandatory->isInEmergency')
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

        $emergency->shouldReceive('setSpacecraft')
            ->with($ship)
            ->once();
        $emergency->shouldReceive('setText')
            ->with($text)
            ->once();
        $emergency->shouldReceive('setDate')
            ->with(Mockery::type('int'))
            ->once();

        $computer->shouldReceive('update')
            ->withNoArgs()
            ->once();

        $shipWrapper->shouldReceive('getComputerSystemDataMandatory->setIsInEmergency')
            ->with(true)
            ->once()
            ->andReturn($computer);

        $this->subject->handle($game);
    }

    public function testPerformSessionCheckReturnsTrue(): void
    {
        $this->assertTrue(
            $this->subject->performSessionCheck()
        );
    }
}
