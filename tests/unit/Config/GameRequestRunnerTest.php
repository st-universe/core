<?php

declare(strict_types=1);

namespace Stu\Config;

use Mockery;
use Stu\Component\Game\ModuleEnum;
use Stu\Component\Logging\GameRequest\GameRequestSaverInterface;
use Stu\Exception\SessionInvalidException;
use Stu\Lib\UuidGeneratorInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\GameSessionInitializerInterface;
use Stu\Orm\Entity\GameRequest;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\GameRequestRepositoryInterface;
use Stu\Orm\Repository\GameTurnRepositoryInterface;
use Stu\StuTestCase;

class GameRequestRunnerTest extends StuTestCase
{
    public function testRunStartsSessionAndDelegatesToController(): void
    {
        $gameController = $this->mock(GameControllerInterface::class);
        $sessionStarter = $this->mock(SessionStarterInterface::class);
        $gameSessionInitializer = $this->mock(GameSessionInitializerInterface::class);
        $gameRequestRepository = $this->mock(GameRequestRepositoryInterface::class);
        $uuidGenerator = $this->mock(UuidGeneratorInterface::class);
        $gameRequest = $this->mock(GameRequest::class);
        $user = $this->mock(User::class);
        $gameRequestSaver = $this->mock(GameRequestSaverInterface::class);
        $gameTurnRepository = $this->mock(GameTurnRepositoryInterface::class);
        $gameTurn = $this->mock(\Stu\Orm\Entity\GameTurn::class);

        $sessionStarter->shouldReceive('start')
            ->withNoArgs()
            ->once();
        $gameRequestRepository->shouldReceive('prototype')
            ->withNoArgs()
            ->once()
            ->andReturn($gameRequest);
        $uuidGenerator->shouldReceive('genV4')
            ->withNoArgs()
            ->once()
            ->andReturn('request-id');
        $gameTurnRepository->shouldReceive('getCurrent')
            ->withNoArgs()
            ->once()
            ->andReturn($gameTurn);
        $gameSessionInitializer->shouldReceive('initialize')
            ->with(ModuleEnum::GAME)
            ->once()
            ->andReturn($user);
        $gameRequest->shouldReceive('setTime')
            ->withArgs([Mockery::type('int')])
            ->once()
            ->andReturnSelf();
        $gameRequest->shouldReceive('setTurnId')
            ->with($gameTurn)
            ->once()
            ->andReturnSelf();
        $gameRequest->shouldReceive('setParameterArray')
            ->withArgs([Mockery::type('array')])
            ->once()
            ->andReturnSelf();
        $gameRequest->shouldReceive('setRequestId')
            ->with('request-id')
            ->once()
            ->andReturnSelf();
        $gameRequest->shouldReceive('setUserId')
            ->with($user)
            ->once()
            ->andReturnSelf();
        $gameController->shouldReceive('main')
            ->with(ModuleEnum::GAME, $gameRequest)
            ->once();
        $gameRequestSaver->shouldReceive('save')
            ->with($gameRequest, false)
            ->once();

        $runner = new GameRequestRunner(
            $gameController,
            $sessionStarter,
            $gameSessionInitializer,
            $gameRequestRepository,
            $uuidGenerator,
            $gameRequestSaver,
            $gameTurnRepository
        );

        $runner->run(ModuleEnum::GAME);
    }

    public function testRunSavesRequestAsErrorWhenSessionIsInvalid(): void
    {
        $gameController = $this->mock(GameControllerInterface::class);
        $sessionStarter = $this->mock(SessionStarterInterface::class);
        $gameSessionInitializer = $this->mock(GameSessionInitializerInterface::class);
        $gameRequestRepository = $this->mock(GameRequestRepositoryInterface::class);
        $uuidGenerator = $this->mock(UuidGeneratorInterface::class);
        $gameRequestSaver = $this->mock(GameRequestSaverInterface::class);
        $gameTurnRepository = $this->mock(GameTurnRepositoryInterface::class);
        $gameTurn = $this->mock(\Stu\Orm\Entity\GameTurn::class);
        $gameRequest = $this->mock(GameRequest::class);

        $sessionStarter->shouldReceive('start')->once();
        $gameRequestRepository->shouldReceive('prototype')->once()->andReturn($gameRequest);
        $uuidGenerator->shouldReceive('genV4')->once()->andReturn('request-id');
        $gameTurnRepository->shouldReceive('getCurrent')->once()->andReturn($gameTurn);
        $gameRequest->shouldReceive('setTime')->once()->andReturnSelf();
        $gameRequest->shouldReceive('setTurnId')->with($gameTurn)->once()->andReturnSelf();
        $gameRequest->shouldReceive('setParameterArray')->once()->andReturnSelf();
        $gameRequest->shouldReceive('setRequestId')->with('request-id')->once()->andReturnSelf();
        $gameSessionInitializer->shouldReceive('initialize')
            ->with(ModuleEnum::GAME)
            ->once()
            ->andThrow(new SessionInvalidException());
        $gameRequestSaver->shouldReceive('save')
            ->with($gameRequest, true)
            ->once();
        $gameController->shouldNotReceive('main');

        $runner = new GameRequestRunner(
            $gameController,
            $sessionStarter,
            $gameSessionInitializer,
            $gameRequestRepository,
            $uuidGenerator,
            $gameRequestSaver,
            $gameTurnRepository
        );

        $runner->run(ModuleEnum::GAME);
    }
}
