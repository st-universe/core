<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\DemotePlayer;

use Mockery\MockInterface;
use Override;
use Stu\Exception\AccessViolationException;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Alliance\Lib\AllianceJobManagerInterface;
use Stu\Module\Alliance\View\Management\Management;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;

class DemotePlayerTest extends StuTestCase
{
    private MockInterface&DemotePlayerRequestInterface $demotePlayerRequest;
    private MockInterface&AllianceActionManagerInterface $allianceActionManager;
    private MockInterface&PrivateMessageSenderInterface $privateMessageSender;
    private MockInterface&UserRepositoryInterface $userRepository;
    private MockInterface&AllianceJobManagerInterface $allianceJobManager;

    private MockInterface&User $user;
    private MockInterface&GameControllerInterface $game;
    private MockInterface&Alliance $alliance;

    private DemotePlayer $subject;

    private int $userId = 666;

    private int $playerId = 33;

    #[Override]
    protected function setUp(): void
    {
        $this->demotePlayerRequest = $this->mock(DemotePlayerRequestInterface::class);
        $this->allianceActionManager = $this->mock(AllianceActionManagerInterface::class);
        $this->privateMessageSender = $this->mock(PrivateMessageSenderInterface::class);
        $this->userRepository = $this->mock(UserRepositoryInterface::class);
        $this->allianceJobManager = $this->mock(AllianceJobManagerInterface::class);

        $this->user = $this->mock(User::class);
        $this->game = $this->mock(GameControllerInterface::class);
        $this->alliance = $this->mock(Alliance::class);

        $this->alliance->shouldReceive('getId')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(424242);

        $this->subject = new DemotePlayer(
            $this->demotePlayerRequest,
            $this->allianceActionManager,
            $this->privateMessageSender,
            $this->userRepository,
            $this->allianceJobManager
        );
    }

    public function testHandleErrorsOnMissingPrivileges(): void
    {
        static::expectException(AccessViolationException::class);

        $this->createBasicExpectation();

        $this->allianceActionManager->shouldReceive('mayEdit')
            ->with($this->alliance, $this->user)
            ->once()
            ->andReturnFalse();

        $this->subject->handle($this->game);
    }

    public function testHandleErrorsIfUserWasNotFound(): void
    {
        static::expectException(AccessViolationException::class);

        $this->createBasicExpectation();

        $this->allianceActionManager->shouldReceive('mayEdit')
            ->with($this->alliance, $this->user)
            ->once()
            ->andReturnTrue();

        $this->userRepository->shouldReceive('find')
            ->with($this->playerId)
            ->once()
            ->andReturnNull();

        $this->subject->handle($this->game);
    }

    public function testHandleErrorsIfPlayerIdIsSameAsUser(): void
    {
        static::expectException(AccessViolationException::class);

        $player = $this->mock(User::class);

        $this->createBasicExpectation();

        $this->allianceActionManager->shouldReceive('mayEdit')
            ->with($this->alliance, $this->user)
            ->once()
            ->andReturnTrue();

        $this->userRepository->shouldReceive('find')
            ->with($this->playerId)
            ->once()
            ->andReturn($player);

        $player->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($this->userId);

        $this->subject->handle($this->game);
    }

    public function testHandleErrorsIfPlayerIsNotInAlliance(): void
    {
        static::expectException(AccessViolationException::class);

        $player = $this->mock(User::class);

        $this->createBasicExpectation();

        $this->allianceActionManager->shouldReceive('mayEdit')
            ->with($this->alliance, $this->user)
            ->once()
            ->andReturnTrue();

        $this->userRepository->shouldReceive('find')
            ->with($this->playerId)
            ->once()
            ->andReturn($player);

        $player->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($this->playerId);
        $player->shouldReceive('getAlliance')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $this->subject->handle($this->game);
    }

    public function testHandleDemotesPlayer(): void
    {
        $player = $this->mock(User::class);

        $allianceName = 'alliance-name';

        $this->createBasicExpectation();

        $this->allianceActionManager->shouldReceive('mayEdit')
            ->with($this->alliance, $this->user)
            ->once()
            ->andReturnTrue();

        $this->userRepository->shouldReceive('find')
            ->with($this->playerId)
            ->once()
            ->andReturn($player);

        $player->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($this->playerId);
        $player->shouldReceive('getAlliance')
            ->withNoArgs()
            ->once()
            ->andReturn($this->alliance);

        $this->allianceJobManager->shouldReceive('removeUserFromAllJobs')
            ->with($player, $this->alliance)
            ->once();

        $this->privateMessageSender->shouldReceive('send')
            ->with(
                $this->userId,
                $this->playerId,
                sprintf(
                    'Du wurdest von Deinem Posten in der Allianz %s entbunden',
                    $allianceName
                )
            )
            ->once();

        $this->alliance->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn($allianceName);

        $this->game->shouldReceive('getInfo->addInformation')
            ->with('Das Mitglied wurde von seinem Posten enthoben')
            ->once();
        $this->game->shouldReceive('setView')
            ->with(Management::VIEW_IDENTIFIER)
            ->once();

        $this->subject->handle($this->game);
    }

    public function createBasicExpectation(): void
    {
        $this->game->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($this->user);

        $this->user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($this->userId);
        $this->user->shouldReceive('getAlliance')
            ->withNoArgs()
            ->once()
            ->andReturn($this->alliance);

        $this->demotePlayerRequest->shouldReceive('getPlayerId')
            ->withNoArgs()
            ->once()
            ->andReturn($this->playerId);
    }
}
