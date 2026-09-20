<?php

declare(strict_types=1);

namespace Stu\Config;

use Mockery;
use Stu\Component\Game\ModuleEnum;
use Stu\Lib\UuidGeneratorInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\GameSessionInitializerInterface;
use Stu\Orm\Entity\GameRequest;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\GameRequestRepositoryInterface;
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
        $gameSessionInitializer->shouldReceive('initialize')
            ->with(ModuleEnum::GAME)
            ->once()
            ->andReturn($user);
        $gameRequest->shouldReceive('setTime')
            ->withArgs([Mockery::type('int')])
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

        $runner = new GameRequestRunner(
            $gameController,
            $sessionStarter,
            $gameSessionInitializer,
            $gameRequestRepository,
            $uuidGenerator
        );

        $runner->run(ModuleEnum::GAME);
    }
}
