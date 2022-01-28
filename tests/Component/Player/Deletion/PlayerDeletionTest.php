<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Stu\Component\Player\Deletion\Handler\PlayerDeletionHandlerInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

class PlayerDeletionTest extends MockeryTestCase
{

    /**
     * @var null|MockInterface|UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var null|MockInterface|PlayerDeletionHandlerInterface
     */
    private $deletionHandler;

    /**
     * @var null|PlayerDeletion
     */
    private $playerDeletion;

    public function setUp(): void
    {
        $this->deletionHandler = Mockery::mock(PlayerDeletionHandlerInterface::class);
        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);

        $this->playerDeletion = new PlayerDeletion(
            $this->userRepository,
            [$this->deletionHandler]
        );
    }

    public function testHandleDeleteableRemovesIdleAndMarkedPlayers(): void
    {
        $player = Mockery::mock(UserInterface::class);

        $this->userRepository->shouldReceive('getDeleteable')
            ->with(
                Mockery::on(function ($value): bool {
                    return $value < time();
                }),
                [101]
            )
            ->once()
            ->andReturn([$player]);

        $this->deletionHandler->shouldReceive('delete')
            ->with($player)
            ->once();

        $this->playerDeletion->handleDeleteable();
    }

    public function testHandleResetRemovesAllActualPlayer(): void
    {
        $player = Mockery::mock(UserInterface::class);

        $this->userRepository->shouldReceive('getActualPlayer')
            ->withNoArgs()
            ->once()
            ->andReturn([$player]);

        $this->deletionHandler->shouldReceive('delete')
            ->with($player)
            ->once();

        $this->playerDeletion->handleReset();
    }
}
