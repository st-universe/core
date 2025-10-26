<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\PromotePlayer;

use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Component\Alliance\Enum\AllianceJobTypeEnum;
use Stu\Component\Game\ModuleEnum;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Alliance\Lib\AllianceJobManagerInterface;
use Stu\Module\Alliance\View\Management\Management;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\AllianceJob;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;

class PromotePlayerTest extends StuTestCase
{
    private MockInterface&PromotePlayerRequestInterface $promotePlayerRequest;
    private MockInterface&AllianceJobRepositoryInterface $allianceJobRepository;
    private MockInterface&AllianceActionManagerInterface $allianceActionManager;
    private MockInterface&PrivateMessageSenderInterface $privateMessageSender;
    private MockInterface&UserRepositoryInterface $userRepository;
    private MockInterface&AllianceJobManagerInterface $allianceJobManager;

    private ActionControllerInterface $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->promotePlayerRequest = $this->mock(PromotePlayerRequestInterface::class);
        $this->allianceJobRepository = $this->mock(AllianceJobRepositoryInterface::class);
        $this->allianceActionManager = $this->mock(AllianceActionManagerInterface::class);
        $this->privateMessageSender = $this->mock(PrivateMessageSenderInterface::class);
        $this->userRepository = $this->mock(UserRepositoryInterface::class);
        $this->allianceJobManager = $this->mock(AllianceJobManagerInterface::class);

        $this->subject = new PromotePlayer(
            $this->promotePlayerRequest,
            $this->allianceJobRepository,
            $this->allianceActionManager,
            $this->privateMessageSender,
            $this->userRepository,
            $this->allianceJobManager
        );
    }

    public static function provideData(): array
    {
        return [
            [true, 'Präsident', 'Du wurdest zum Präsident der Allianz ALLYNAME ernannt'],
            [false, 'Vize-Präsident', 'Du wurdest zum Vize-Präsident der Allianz ALLYNAME ernannt'],
            [false, 'Außenminister', 'Du wurdest zum Außenminister der Allianz ALLYNAME ernannt']
        ];
    }

    #[DataProvider('provideData')]
    public function testHandle(
        bool $isFounderJob,
        string $jobTitle,
        string $expectedPmText
    ): void {

        $alliance = $this->mock(Alliance::class);
        $user = $this->mock(User::class);
        $promotedPlayer = $this->mock(User::class);
        $job = $this->mock(AllianceJob::class);
        $founderJob = $this->mock(AllianceJob::class);
        $game = $this->mock(GameControllerInterface::class);

        $alliance->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(555);
        $alliance->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('ALLYNAME');
        $alliance->shouldReceive('getFounder')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn($founderJob);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(42);
        $user->shouldReceive('getAlliance')
            ->withNoArgs()
            ->once()
            ->andReturn($alliance);

        $promotedPlayer->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(55);
        $promotedPlayer->shouldReceive('getAlliance')
            ->withNoArgs()
            ->once()
            ->andReturn($alliance);

        $job->shouldReceive('hasFounderPermission')
            ->withNoArgs()
            ->once()
            ->andReturn($isFounderJob);
        $job->shouldReceive('getAlliance')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn($alliance);
        $job->shouldReceive('getTitle')
            ->withNoArgs()
            ->once()
            ->andReturn($jobTitle);

        if ($isFounderJob) {
            $founderJob->shouldReceive('getUsers')
                ->withNoArgs()
                ->once()
                ->andReturn([$user]);

            $this->allianceJobManager->shouldReceive('hasUserFounderPermission')
                ->with($user, $alliance)
                ->once()
                ->andReturnTrue();
            $this->allianceJobManager->shouldReceive('removeUserFromJob')
                ->with($user, $founderJob)
                ->once();
        }

        $game->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $game->shouldReceive('getInfo->addInformation')
            ->with('Das Mitglied wurde befördert')
            ->once();
        $game->shouldReceive('setView')
            ->with($isFounderJob ? ModuleEnum::ALLIANCE : Management::VIEW_IDENTIFIER)
            ->once();

        $this->promotePlayerRequest->shouldReceive('getPlayerId')
            ->withNoArgs()
            ->once()
            ->andReturn(55);
        $this->promotePlayerRequest->shouldReceive('getPromotionType')
            ->withNoArgs()
            ->once()
            ->andReturn(99);

        $this->allianceActionManager->shouldReceive('mayEdit')
            ->with($alliance, $user)
            ->once()
            ->andReturn(true);

        $this->userRepository->shouldReceive('find')
            ->with(55)
            ->once()
            ->andReturn($promotedPlayer);

        $this->allianceJobRepository->shouldReceive('find')
            ->with(99)
            ->once()
            ->andReturn($job);

        $this->allianceJobManager->shouldReceive('removeUserFromAllJobs')
            ->with($promotedPlayer, $alliance)
            ->once();
        $this->allianceJobManager->shouldReceive('assignUserToJob')
            ->with($promotedPlayer, $job)
            ->once();

        $this->privateMessageSender->shouldReceive('send')
            ->with(42, 55, $expectedPmText)
            ->once()
            ->andReturn($promotedPlayer);

        $this->subject->handle($game);
    }
}
