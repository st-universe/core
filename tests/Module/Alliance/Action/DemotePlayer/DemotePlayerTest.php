<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\DemotePlayer;

use Override;
use Mockery\MockInterface;
use Stu\Exception\AccessViolation;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Alliance\View\Management\Management;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Orm\Entity\AllianceInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;

class DemotePlayerTest extends StuTestCase
{
    /** @var MockInterface&DemotePlayerRequestInterface */
    private MockInterface $demotePlayerRequest;

    /** @var MockInterface&AllianceJobRepositoryInterface */
    private MockInterface $allianceJobRepository;

    /** @var MockInterface&AllianceActionManagerInterface */
    private MockInterface $allianceActionManager;

    /** @var MockInterface&PrivateMessageSenderInterface */
    private MockInterface $privateMessageSender;

    /** @var MockInterface&UserRepositoryInterface */
    private MockInterface $userRepository;

    /** @var MockInterface&UserInterface */
    private MockInterface $user;

    /** @var MockInterface&GameControllerInterface */
    private MockInterface $game;

    /** @var MockInterface&AllianceInterface */
    private MockInterface $alliance;

    private DemotePlayer $subject;

    private int $userId = 666;

    private int $playerId = 33;

    #[Override]
    protected function setUp(): void
    {
        $this->demotePlayerRequest = $this->mock(DemotePlayerRequestInterface::class);
        $this->allianceJobRepository = $this->mock(AllianceJobRepositoryInterface::class);
        $this->allianceActionManager = $this->mock(AllianceActionManagerInterface::class);
        $this->privateMessageSender = $this->mock(PrivateMessageSenderInterface::class);
        $this->userRepository = $this->mock(UserRepositoryInterface::class);

        $this->user = $this->mock(UserInterface::class);
        $this->game = $this->mock(GameControllerInterface::class);
        $this->alliance = $this->mock(AllianceInterface::class);

        $this->subject = new DemotePlayer(
            $this->demotePlayerRequest,
            $this->allianceJobRepository,
            $this->allianceActionManager,
            $this->privateMessageSender,
            $this->userRepository
        );
    }

    public function testHandleErrorsOnMissingPrivileges(): void
    {
        static::expectException(AccessViolation::class);

        $this->createBasicExpectation();

        $this->allianceActionManager->shouldReceive('mayEdit')
            ->with($this->alliance, $this->user)
            ->once()
            ->andReturnFalse();

        $this->subject->handle($this->game);
    }

    public function testHandleErrorsIfUserWasNotFound(): void
    {
        static::expectException(AccessViolation::class);

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
        static::expectException(AccessViolation::class);

        $player = $this->mock(UserInterface::class);

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
        static::expectException(AccessViolation::class);

        $player = $this->mock(UserInterface::class);

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
        $player = $this->mock(UserInterface::class);

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

        $this->allianceJobRepository->shouldReceive('truncateByUser')
            ->with($this->playerId)
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

        $this->game->shouldReceive('addInformation')
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
