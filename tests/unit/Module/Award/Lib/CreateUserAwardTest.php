<?php

declare(strict_types=1);

namespace Stu\Module\Award\Lib;

use Mockery\MockInterface;
use Stu\Module\Prestige\Lib\CreatePrestigeLogInterface;
use Stu\Orm\Entity\Award;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserAward;
use Stu\Orm\Repository\UserAwardRepositoryInterface;
use Stu\StuTestCase;

class CreateUserAwardTest extends StuTestCase
{
    private MockInterface&UserAwardRepositoryInterface $userAwardRepository;

    private MockInterface&CreatePrestigeLogInterface $createPrestigeLog;

    private CreateUserAwardInterface $subject;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->userAwardRepository = $this->mock(UserAwardRepositoryInterface::class);
        $this->createPrestigeLog = $this->mock(CreatePrestigeLogInterface::class);

        $this->subject = new CreateUserAward(
            $this->userAwardRepository,
            $this->createPrestigeLog
        );
    }

    public function testCreateAwardForUserCreatesNewAward(): void
    {
        $user = $this->mock(User::class);
        $award = $this->createAward(50, 7, 'Kazon Top 10');
        $userAward = new UserAward();

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->userAwardRepository->shouldReceive('findOneBy')
            ->with(['user_id' => 42, 'award_id' => 50])
            ->once()
            ->andReturn(null);

        $this->userAwardRepository->shouldReceive('prototype')
            ->withNoArgs()
            ->once()
            ->andReturn($userAward);

        $this->userAwardRepository->shouldReceive('save')
            ->withArgs(fn (UserAward $savedAward): bool => $savedAward === $userAward && $savedAward->getCount() === 1)
            ->once();

        $this->createPrestigeLog->shouldReceive('createLog')
            ->with(7, '7 Prestige erhalten für den Erhalt des Awards "Kazon Top 10"', $user, \Mockery::type('int'))
            ->once();

        $this->subject->createAwardForUser($user, $award);
    }

    public function testCreateAwardForUserKeepsExistingAwardOneTimeByDefault(): void
    {
        $user = $this->mock(User::class);
        $award = $this->createAward(50, 7, 'Kazon Top 10');
        $existingAward = (new UserAward())->setCount(null);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->userAwardRepository->shouldReceive('findOneBy')
            ->with(['user_id' => 42, 'award_id' => 50])
            ->once()
            ->andReturn($existingAward);

        $this->userAwardRepository->shouldReceive('save')
            ->withArgs(fn (UserAward $savedAward): bool => $savedAward === $existingAward && $savedAward->getCount() === 1)
            ->once();

        $this->createPrestigeLog->shouldReceive('createLog')
            ->never();

        $this->subject->createAwardForUser($user, $award);
    }

    public function testCreateAwardForUserIncrementsExistingAwardIfRequested(): void
    {
        $user = $this->mock(User::class);
        $award = $this->createAward(50, 7, 'Kazon Top 10');
        $existingAward = (new UserAward())->setCount(2);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->userAwardRepository->shouldReceive('findOneBy')
            ->with(['user_id' => 42, 'award_id' => 50])
            ->once()
            ->andReturn($existingAward);

        $this->userAwardRepository->shouldReceive('save')
            ->withArgs(fn (UserAward $savedAward): bool => $savedAward === $existingAward && $savedAward->getCount() === 3)
            ->once();

        $this->createPrestigeLog->shouldReceive('createLog')
            ->with(7, '7 Prestige erhalten für den Erhalt des Awards "Kazon Top 10"', $user, \Mockery::type('int'))
            ->once();

        $this->subject->createAwardForUser($user, $award, true);
    }

    private function createAward(int $id, int $prestige, string $description): Award
    {
        return (new Award())
            ->setId($id)
            ->setPrestige($prestige)
            ->setDescription($description);
    }
}
