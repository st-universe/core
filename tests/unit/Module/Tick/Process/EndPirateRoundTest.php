<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Process;

use Mockery\MockInterface;
use Stu\Component\Player\UserAwardEnum;
use Stu\Module\Award\Lib\CreateUserAwardInterface;
use Stu\Module\History\Lib\EntryCreatorInterface;
use Stu\Module\Prestige\Lib\CreatePrestigeLogInterface;
use Stu\Orm\Entity\Award;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserPirateRound;
use Stu\Orm\Repository\AwardRepositoryInterface;
use Stu\Orm\Repository\LayerRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\PirateRoundRepositoryInterface;
use Stu\Orm\Repository\UserPirateRoundRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;

class EndPirateRoundTest extends StuTestCase
{
    private MockInterface&PirateRoundRepositoryInterface $pirateRoundRepository;

    private MockInterface&UserPirateRoundRepositoryInterface $userPirateRoundRepository;

    private MockInterface&UserRepositoryInterface $userRepository;

    private MockInterface&EntryCreatorInterface $entryCreator;

    private MockInterface&LayerRepositoryInterface $layerRepository;

    private MockInterface&MapRepositoryInterface $mapRepository;

    private MockInterface&CreatePrestigeLogInterface $createPrestigeLog;

    private MockInterface&AwardRepositoryInterface $awardRepository;

    private MockInterface&CreateUserAwardInterface $createUserAward;

    private EndPirateRound $subject;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->pirateRoundRepository = $this->mock(PirateRoundRepositoryInterface::class);
        $this->userPirateRoundRepository = $this->mock(UserPirateRoundRepositoryInterface::class);
        $this->userRepository = $this->mock(UserRepositoryInterface::class);
        $this->entryCreator = $this->mock(EntryCreatorInterface::class);
        $this->layerRepository = $this->mock(LayerRepositoryInterface::class);
        $this->mapRepository = $this->mock(MapRepositoryInterface::class);
        $this->createPrestigeLog = $this->mock(CreatePrestigeLogInterface::class);
        $this->awardRepository = $this->mock(AwardRepositoryInterface::class);
        $this->createUserAward = $this->mock(CreateUserAwardInterface::class);

        $this->subject = new EndPirateRound(
            $this->pirateRoundRepository,
            $this->userPirateRoundRepository,
            $this->userRepository,
            $this->entryCreator,
            $this->layerRepository,
            $this->mapRepository,
            $this->createPrestigeLog,
            $this->awardRepository,
            $this->createUserAward
        );
    }

    public function testDistributeAwardRewardsAssignsPlacementAwardsToTopTen(): void
    {
        $users = [];
        $userPirateRounds = [];
        for ($i = 0; $i < 11; $i++) {
            $users[$i] = $this->mock(User::class);
            $userPirateRounds[$i] = $this->mock(UserPirateRound::class);
            $userPirateRounds[$i]->shouldReceive('getUser')
                ->withNoArgs()
                ->zeroOrMoreTimes()
                ->andReturn($users[$i]);
        }

        $awards = [
            UserAwardEnum::KAZON_FIRST_PLACE => $this->createAward(UserAwardEnum::KAZON_FIRST_PLACE),
            UserAwardEnum::KAZON_SECOND_PLACE => $this->createAward(UserAwardEnum::KAZON_SECOND_PLACE),
            UserAwardEnum::KAZON_THIRD_PLACE => $this->createAward(UserAwardEnum::KAZON_THIRD_PLACE),
            UserAwardEnum::KAZON_TOP_TEN => $this->createAward(UserAwardEnum::KAZON_TOP_TEN),
        ];
        $requestedAwardIds = [];
        $createdAwards = [];

        $this->userPirateRoundRepository->shouldReceive('findByPirateRound')
            ->with(123)
            ->once()
            ->andReturn($userPirateRounds);

        $this->awardRepository->shouldReceive('find')
            ->withArgs(function (int $awardId) use (&$requestedAwardIds): bool {
                $requestedAwardIds[] = $awardId;
                return true;
            })
            ->times(10)
            ->andReturnUsing(fn (int $awardId): Award => $awards[$awardId]);

        $this->createUserAward->shouldReceive('createAwardForUser')
            ->withArgs(function (User $user, Award $award, bool $incrementExisting) use (&$createdAwards): bool {
                $createdAwards[] = [$user, $award, $incrementExisting];
                return true;
            })
            ->times(10);

        $method = (new \ReflectionClass($this->subject))->getMethod('distributeAwardRewards');
        $method->invoke($this->subject, 123);

        $this->assertSame(
            [
                UserAwardEnum::KAZON_FIRST_PLACE,
                UserAwardEnum::KAZON_SECOND_PLACE,
                UserAwardEnum::KAZON_THIRD_PLACE,
                UserAwardEnum::KAZON_TOP_TEN,
                UserAwardEnum::KAZON_TOP_TEN,
                UserAwardEnum::KAZON_TOP_TEN,
                UserAwardEnum::KAZON_TOP_TEN,
                UserAwardEnum::KAZON_TOP_TEN,
                UserAwardEnum::KAZON_TOP_TEN,
                UserAwardEnum::KAZON_TOP_TEN,
            ],
            $requestedAwardIds
        );

        $this->assertCount(10, $createdAwards);
        $this->assertSame([$users[0], $awards[UserAwardEnum::KAZON_FIRST_PLACE], true], $createdAwards[0]);
        $this->assertSame([$users[1], $awards[UserAwardEnum::KAZON_SECOND_PLACE], true], $createdAwards[1]);
        $this->assertSame([$users[2], $awards[UserAwardEnum::KAZON_THIRD_PLACE], true], $createdAwards[2]);

        for ($i = 3; $i < 10; $i++) {
            $this->assertSame([$users[$i], $awards[UserAwardEnum::KAZON_TOP_TEN], true], $createdAwards[$i]);
        }
    }

    private function createAward(int $id): Award
    {
        return (new Award())->setId($id);
    }
}
