<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion;

use JBBCode\Parser;
use Mockery;
use Mockery\MockInterface;
use Override;
use Stu\Component\Player\Deletion\Handler\PlayerDeletionHandlerInterface;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Control\StuTime;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;

class PlayerDeletionTest extends StuTestCase
{
    private MockInterface&UserRepositoryInterface $userRepository;
    private MockInterface&StuConfigInterface $config;
    private MockInterface&Parser $bbCodeParser;
    private MockInterface&StuTime $stuTime;
    private MockInterface&LoggerUtilInterface $loggerUtil;
    private MockInterface&PlayerDeletionHandlerInterface $deletionHandler;

    private PlayerDeletionInterface $playerDeletion;

    #[Override]
    public function setUp(): void
    {
        $this->userRepository = $this->mock(UserRepositoryInterface::class);
        $this->config = $this->mock(StuConfigInterface::class);
        $this->bbCodeParser = $this->mock(Parser::class);
        $this->stuTime = $this->mock(StuTime::class);
        $this->deletionHandler = $this->mock(PlayerDeletionHandlerInterface::class);
        $this->loggerUtil = $this->mock(LoggerUtilInterface::class);

        $loggerUtilFactory = $this->mock(LoggerUtilFactoryInterface::class);
        $loggerUtilFactory->shouldReceive('getLoggerUtil')
            ->withNoArgs()
            ->once()
            ->andReturn($this->loggerUtil);

        $this->playerDeletion = new PlayerDeletion(
            $this->userRepository,
            $this->config,
            $this->bbCodeParser,
            $this->stuTime,
            [$this->deletionHandler],
            $loggerUtilFactory
        );
    }

    public function testHandleDeleteableRemovesIdleAndMarkedPlayers(): void
    {
        $idlePlayer = $this->mock(User::class);
        $player = $this->mock(User::class);

        $this->stuTime->shouldReceive('time')
            ->withNoArgs()
            ->once()
            ->andReturn(4242424242);

        $this->loggerUtil->shouldReceive('init')
            ->with('DEL', LoggerEnum::LEVEL_ERROR)
            ->once();

        $this->userRepository->shouldReceive('getIdleRegistrations')
            ->with(
                Mockery::on(fn($value): bool => $value === 4242424242 - PlayerDeletion::USER_IDLE_REGISTRATION)
            )
            ->once()
            ->andReturn([111 => $idlePlayer]);

        $this->config->shouldReceive('getGameSettings->getAdminIds')
            ->withNoArgs()
            ->andReturn([101]);

        $this->userRepository->shouldReceive('getDeleteable')
            ->with(
                Mockery::on(fn($value): bool => $value === 4242424242 - PlayerDeletion::USER_IDLE_TIME),
                Mockery::on(fn($value): bool => $value === 4242424242 - PlayerDeletion::USER_IDLE_TIME_VACATION),
                [101]
            )
            ->once()
            ->andReturn([222 => $player]);

        $deletedPlayers = [111 => $idlePlayer, 222 => $player];

        foreach ($deletedPlayers as $key => $player) {
            $player->shouldReceive('getId')
                ->withNoArgs()
                ->once()
                ->andReturn($key);
            $player->shouldReceive('getName')
                ->withNoArgs()
                ->once()
                ->andReturn('foo' . $key);
            $player->shouldReceive('getRegistration->getDeletionMark')
                ->withNoArgs()
                ->once()
                ->andReturn(666);
            $this->bbCodeParser->shouldReceive('parse')
                ->with('foo' . $key)
                ->once()
                ->andReturnSelf();

            $this->deletionHandler->shouldReceive('delete')
                ->with($player)
                ->once();

            //LOGGER STUFF
            $this->loggerUtil->shouldReceive('log')
                ->with('deleting userId: ' . $key)
                ->once();
            $this->loggerUtil->shouldReceive('log')
                ->with('deleted user (id: ' . $key . ', name: bar, delmark: 666)')
                ->once();
        }

        $this->bbCodeParser->shouldReceive('getAsText')
            ->with()
            ->twice()
            ->andReturn('bar');

        $this->playerDeletion->handleDeleteable();
    }

    public function testHandleResetRemovesAllActualPlayer(): void
    {
        $player = $this->mock(User::class);

        $this->userRepository->shouldReceive('getNonNpcList')
            ->withNoArgs()
            ->once()
            ->andReturn([$player]);

        $player->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(1);
        $player->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('foo1');
        $player->shouldReceive('getRegistration->getDeletionMark')
            ->withNoArgs()
            ->once()
            ->andReturn(666);
        $this->bbCodeParser->shouldReceive('parse')
            ->with('foo1')
            ->once()
            ->andReturnSelf();

        $this->deletionHandler->shouldReceive('delete')
            ->with($player)
            ->once();

        //LOGGER STUFF
        $this->loggerUtil->shouldReceive('log')
            ->with('deleting userId: 1')
            ->once();
        $this->loggerUtil->shouldReceive('log')
            ->with('deleted user (id: 1, name: bar, delmark: 666)')
            ->once();

        $this->bbCodeParser->shouldReceive('getAsText')
            ->with()
            ->once()
            ->andReturn('bar');

        $this->playerDeletion->handleReset();
    }
}
