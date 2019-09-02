<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\AddBoard;

use AccessViolation;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Alliance\View\Boards\Boards;
use Stu\Orm\Repository\AllianceBoardRepositoryInterface;

final class AddBoard implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_ADD_BOARD';

    private $addBoardRequest;

    private $allianceBoardRepository;

    public function __construct(
        AddBoardRequestInterface $addBoardRequest,
        AllianceBoardRepositoryInterface $allianceBoardRepository
    ) {
        $this->addBoardRequest = $addBoardRequest;
        $this->allianceBoardRepository = $allianceBoardRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();

        if (!$alliance->currentUserMayEdit()) {
            throw new AccessViolation();
        }

        $game->setView(Boards::VIEW_IDENTIFIER);

        $name = $this->addBoardRequest->getBoardName();

        if (mb_strlen($name) < 5) {
            $game->addInformation(_('Der Name muss mindestens 5 Zeichen lang sein'));
            return;
        }

        $board = $this->allianceBoardRepository->prototype();
        $board->setAllianceId((int) $alliance->getId());
        $board->setName($name);

        $this->allianceBoardRepository->save($board);

        $game->addInformation(_('Das Forum wurde erstellt'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
