<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\AddBoard;

use Mockery\MockInterface;
use Override;
use Stu\Exception\AccessViolationException;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Alliance\View\Boards\Boards;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\AllianceBoardInterface;
use Stu\Orm\Entity\AllianceInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\AllianceBoardRepositoryInterface;
use Stu\StuTestCase;

class AddBoardTest extends StuTestCase
{
    private MockInterface&AddBoardRequestInterface $addBoardRequest;

    private MockInterface&AllianceBoardRepositoryInterface $allianceBoardRepository;

    private MockInterface&AllianceActionManagerInterface $allianceActionManager;

    private AddBoard $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->addBoardRequest = $this->mock(AddBoardRequestInterface::class);
        $this->allianceBoardRepository = $this->mock(AllianceBoardRepositoryInterface::class);
        $this->allianceActionManager = $this->mock(AllianceActionManagerInterface::class);

        $this->subject = new AddBoard(
            $this->addBoardRequest,
            $this->allianceBoardRepository,
            $this->allianceActionManager
        );
    }

    public function testHandleThrowsIfUserHasNoAlliance(): void
    {
        static::expectException(AccessViolationException::class);

        $game = $this->mock(GameControllerInterface::class);

        $game->shouldReceive('getUser->getAlliance')
            ->withNoArgs()
            ->once()
            ->andReturnNull();

        $this->subject->handle($game);
    }

    public function testHandleThrowsIfUserMayNotEditAlliance(): void
    {
        static::expectException(AccessViolationException::class);

        $user = $this->mock(UserInterface::class);
        $game = $this->mock(GameControllerInterface::class);
        $alliance = $this->mock(AllianceInterface::class);

        $game->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);

        $user->shouldReceive('getAlliance')
            ->withNoArgs()
            ->once()
            ->andReturn($alliance);

        $this->allianceActionManager->shouldReceive('mayEdit')
            ->with($alliance, $user)
            ->once()
            ->andReturnFalse();

        $this->subject->handle($game);
    }

    public function testHandleErrorsIfNameIsTooShort(): void
    {
        $user = $this->mock(UserInterface::class);
        $game = $this->mock(GameControllerInterface::class);
        $alliance = $this->mock(AllianceInterface::class);

        $game->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);
        $game->shouldReceive('setView')
            ->with(Boards::VIEW_IDENTIFIER)
            ->once();
        $game->shouldReceive('addInformation')
            ->with('Der Name muss mindestens 5 Zeichen lang sein')
            ->once();

        $user->shouldReceive('getAlliance')
            ->withNoArgs()
            ->once()
            ->andReturn($alliance);

        $this->allianceActionManager->shouldReceive('mayEdit')
            ->with($alliance, $user)
            ->once()
            ->andReturnTrue();

        $this->addBoardRequest->shouldReceive('getBoardName')
            ->withNoArgs()
            ->once()
            ->andReturn('abcd');

        $this->subject->handle($game);
    }

    public function testHandleCreatesBoard(): void
    {
        $user = $this->mock(UserInterface::class);
        $game = $this->mock(GameControllerInterface::class);
        $alliance = $this->mock(AllianceInterface::class);
        $board = $this->mock(AllianceBoardInterface::class);

        $name = 'abcdc';

        $game->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);
        $game->shouldReceive('setView')
            ->with(Boards::VIEW_IDENTIFIER)
            ->once();
        $game->shouldReceive('addInformation')
            ->with('Das Forum wurde erstellt')
            ->once();

        $user->shouldReceive('getAlliance')
            ->withNoArgs()
            ->once()
            ->andReturn($alliance);

        $this->allianceActionManager->shouldReceive('mayEdit')
            ->with($alliance, $user)
            ->once()
            ->andReturnTrue();

        $this->addBoardRequest->shouldReceive('getBoardName')
            ->withNoArgs()
            ->once()
            ->andReturn($name);

        $this->allianceBoardRepository->shouldReceive('prototype')
            ->withNoArgs()
            ->once()
            ->andReturn($board);
        $this->allianceBoardRepository->shouldReceive('save')
            ->with($board)
            ->once();

        $board->shouldReceive('setAlliance')
            ->with($alliance)
            ->once();
        $board->shouldReceive('setName')
            ->with($name)
            ->once();

        $this->subject->handle($game);
    }

    public function testPerformSessionCheckReturnsFalse(): void
    {
        $this->assertFalse(
            $this->subject->performSessionCheck()
        );
    }
}
