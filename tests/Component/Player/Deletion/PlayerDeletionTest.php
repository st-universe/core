<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion;

use JBBCode\Parser;
use Mockery;
use Mockery\MockInterface;
use Override;
use Noodlehaus\ConfigInterface;
use Stu\Component\Player\Deletion\Handler\PlayerDeletionHandlerInterface;
use Stu\Lib\Mail\MailFactoryInterface;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;


class PlayerDeletionTest extends StuTestCase
{
    private MockInterface|ConfigInterface $configs;

    private MockInterface|UserRepositoryInterface $userRepository;

    private MockInterface|StuConfigInterface $config;

    private MockInterface|LoggerUtilInterface $loggerUtil;

    private MockInterface|Parser $bbCodeParser;

    private MockInterface|PlayerDeletionHandlerInterface $deletionHandler;

    private PlayerDeletionInterface $playerDeletion;

    private MockInterface|MailFactoryInterface $mailFactory;

    #[Override]
    public function setUp(): void
    {
        $this->configs = $this->mock(ConfigInterface::class);
        $this->userRepository = $this->mock(UserRepositoryInterface::class);
        $this->config = $this->mock(StuConfigInterface::class);
        $this->loggerUtil = $this->mock(LoggerUtilInterface::class);
        $this->bbCodeParser = $this->mock(Parser::class);
        $this->deletionHandler = $this->mock(PlayerDeletionHandlerInterface::class);
        $this->mailFactory = $this->mock(MailFactoryInterface::class);

        $loggerUtilFactory = $this->mock(LoggerUtilFactoryInterface::class);
        $loggerUtilFactory->shouldReceive('getLoggerUtil')
            ->withNoArgs()
            ->once()
            ->andReturn($this->loggerUtil);

        $this->playerDeletion = new PlayerDeletion(
            $this->configs,
            $this->userRepository,
            $this->config,
            $loggerUtilFactory,
            $this->bbCodeParser,
            [$this->deletionHandler],
            $this->mailFactory
        );
    }

    public function testHandleDeleteableRemovesIdleAndMarkedPlayers(): void
    {
        $idlePlayer = $this->mock(UserInterface::class);
        $player = $this->mock(UserInterface::class);

        $this->loggerUtil->shouldReceive('init')
            ->with('DEL', LoggerEnum::LEVEL_ERROR)
            ->once();

        $this->userRepository->shouldReceive('getIdleRegistrations')
            ->atLeast()->times(2)
            ->andReturn([$idlePlayer], [], []);


        $this->config->shouldReceive('getGameSettings->getAdminIds')
            ->withNoArgs()
            ->andReturn([101]);

        $this->userRepository->shouldReceive('getDeleteable')
            ->times(2)
            ->andReturn([$player], [], []);

        $this->loggerUtil->shouldReceive('init')
            ->with('mail', LoggerEnum::LEVEL_ERROR)
            ->zeroOrMoreTimes();

        $this->loggerUtil->shouldReceive('log')
            ->with(Mockery::pattern('/Unable to send mail:.*/'))
            ->zeroOrMoreTimes();



        $deletedPlayers = [1 => $idlePlayer, 2 => $player];

        foreach ($deletedPlayers as $key => $player) {
            $player->shouldReceive('getId')
                ->withNoArgs()
                ->once()
                ->andReturn($key);
            $player->shouldReceive('getName')
                ->withNoArgs()
                ->twice()
                ->andReturn('foo' . $key);
            $player->shouldReceive('getDeletionMark')
                ->withNoArgs()
                ->once()
                ->andReturn(666);
            $player->shouldReceive('getEmail')
                ->withNoArgs()
                ->once()
                ->andReturn('player' . $key . '@example.com');
            $this->bbCodeParser->shouldReceive('parse')
                ->with('foo' . $key)
                ->twice()
                ->andReturnSelf();

            $this->deletionHandler->shouldReceive('delete')
                ->with($player)
                ->once();

            $this->loggerUtil->shouldReceive('log')
                ->with('deleting userId: ' . $key)
                ->once();
            $this->loggerUtil->shouldReceive('log')
                ->with('deleted user (id: ' . $key . ', name: bar, delmark: 666)')
                ->once();
        }

        $this->bbCodeParser->shouldReceive('getAsText')
            ->with()
            ->times(4)
            ->andReturn('bar');

        $this->configs->shouldReceive('get')
            ->with('game.email_sender_address')
            ->atLeast()->twice()
            ->andReturn('sender@example.com');
        $this->configs->shouldReceive('get')
            ->with('game.base_url')
            ->times(2)
            ->andReturn('http://example.com');


        $this->playerDeletion->handleDeleteable();
    }

    public function testHandleResetRemovesAllActualPlayer(): void
    {
        $player = $this->mock(UserInterface::class);

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
        $player->shouldReceive('getDeletionMark')
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
