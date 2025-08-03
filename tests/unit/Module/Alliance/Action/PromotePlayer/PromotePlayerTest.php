<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\PromotePlayer;

use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Component\Alliance\Enum\AllianceJobTypeEnum;
use Stu\Component\Game\ModuleEnum;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
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

    private ActionControllerInterface $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->promotePlayerRequest = $this->mock(PromotePlayerRequestInterface::class);
        $this->allianceJobRepository = $this->mock(AllianceJobRepositoryInterface::class);
        $this->allianceActionManager = $this->mock(AllianceActionManagerInterface::class);
        $this->privateMessageSender = $this->mock(PrivateMessageSenderInterface::class);
        $this->userRepository = $this->mock(UserRepositoryInterface::class);

        $this->subject = new PromotePlayer(
            $this->promotePlayerRequest,
            $this->allianceJobRepository,
            $this->allianceActionManager,
            $this->privateMessageSender,
            $this->userRepository
        );
    }

    public static function provideData(): array
    {
        return [
            [AllianceJobTypeEnum::FOUNDER, 'Du wurdest zum neuen Präsidenten der Allianz ALLYNAME ernannt'],
            [AllianceJobTypeEnum::SUCCESSOR, 'Du wurdest zum neuen Vize-Präsidenten der Allianz ALLYNAME ernannt'],
            [AllianceJobTypeEnum::DIPLOMATIC, 'Du wurdest zum neuen Außenminister der Allianz ALLYNAME ernannt']
        ];
    }

    #[DataProvider('provideData')]
    public function testHandle(
        AllianceJobTypeEnum $jobType,
        string $expectedPmText
    ): void {

        $alliance = $this->mock(Alliance::class);
        $user = $this->mock(User::class);
        $promotedPlayer = $this->mock(User::class);
        $founderJob = $this->mock(AllianceJob::class);
        $game = $this->mock(GameControllerInterface::class);

        $alliance->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('ALLYNAME');
        $alliance->shouldReceive('getFounder')
            ->withNoArgs()
            ->once()
            ->andReturn($founderJob);
        $alliance->shouldReceive('getJobs->remove')
            ->with($jobType->value)
            ->once();

        $user->shouldReceive('getAlliance')
            ->withNoArgs()
            ->once()
            ->andReturn($alliance);
        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $promotedPlayer->shouldReceive('getAlliance')
            ->withNoArgs()
            ->once()
            ->andReturn($alliance);

        $founderJob->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $founderJob->shouldReceive('getAlliance')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn($alliance);

        $game->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $game->shouldReceive('addInformation')
            ->with('Das Mitglied wurde befördert')
            ->once();
        $game->shouldReceive('setView')
            ->with(Management::VIEW_IDENTIFIER)
            ->once();

        if ($jobType === AllianceJobTypeEnum::FOUNDER) {
            $game->shouldReceive('setView')
                ->with(ModuleEnum::ALLIANCE)
                ->once();
        }

        $this->promotePlayerRequest->shouldReceive('getPlayerId')
            ->withNoArgs()
            ->once()
            ->andReturn(55);
        $this->promotePlayerRequest->shouldReceive('getPromotionType')
            ->withNoArgs()
            ->once()
            ->andReturn($jobType->value);

        $this->allianceActionManager->shouldReceive('mayEdit')
            ->with($alliance, $user)
            ->once()
            ->andReturn(true);
        $this->allianceActionManager->shouldReceive('setJobForUser')
            ->with($alliance, $promotedPlayer, $jobType)
            ->once();

        $this->userRepository->shouldReceive('find')
            ->with(55)
            ->once()
            ->andReturn($promotedPlayer);

        $this->allianceJobRepository->shouldReceive('truncateByUser')
            ->with(55)
            ->once();

        $this->privateMessageSender->shouldReceive('send')
            ->with(42, 55, $expectedPmText)
            ->once()
            ->andReturn($promotedPlayer);

        $this->subject->handle($game);
    }
}
