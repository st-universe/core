<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\LatinumRanking;

use Mockery;
use Mockery\MockInterface;
use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;
use Traversable;

class LatinumRankingTest extends StuTestCase
{
    private MockInterface&StorageRepositoryInterface $storageRepository;

    private MockInterface&UserRepositoryInterface $userRepository;

    private LatinumRanking $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->storageRepository = $this->mock(StorageRepositoryInterface::class);
        $this->userRepository = $this->mock(UserRepositoryInterface::class);

        $this->subject = new LatinumRanking(
            $this->storageRepository,
            $this->userRepository
        );
    }

    public function testHandleRenders(): void
    {
        $game = $this->mock(GameControllerInterface::class);
        $user = $this->mock(User::class);

        $userId = 666;
        $amount = 42;

        $this->storageRepository->shouldReceive('getLatinumTop10')
            ->withNoArgs()
            ->once()
            ->andReturn([[
                'user_id' => $userId,
                'amount' => $amount,
            ]]);

        $this->userRepository->shouldReceive('find')
            ->with($userId)
            ->once()
            ->andReturn($user);

        $game->shouldReceive('setNavigation')
            ->with([
                [
                    'url' => 'database.php',
                    'title' => 'Datenbank'
                ],
                [
                    'url' => sprintf('database.php?%s=1', LatinumRanking::VIEW_IDENTIFIER),
                    'title' => 'Die 10 Söhne des Nagus'
                ]
            ])
            ->once();
        $game->shouldReceive('setPageTitle')
            ->with('/ Datenbank / Die 10 Söhne des Nagus')
            ->once();
        $game->shouldReceive('setViewTemplate')
            ->with('html/database/highscores/topLatinum.twig')
            ->once();
        $game->shouldReceive('setTemplateVar')
            ->with('NAGUS_LIST', Mockery::on(fn(Traversable $list): bool => iterator_to_array($list) === [[
                'user' => $user,
                'amount' => $amount
            ]]))
            ->once();

        $this->subject->handle($game);
    }
}
