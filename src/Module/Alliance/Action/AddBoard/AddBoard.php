<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\AddBoard;

use AccessViolation;
use AllianceBoardData;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
use Stu\Module\Alliance\View\Boards\Boards;

final class AddBoard implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_ADD_BOARD';

    private $addBoardRequest;

    public function __construct(
        AddBoardRequestInterface $addBoardRequest
    ) {
        $this->addBoardRequest = $addBoardRequest;
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

        $board = new AllianceBoardData();
        $board->setAllianceId($alliance->getId());
        $board->setName($name);
        $board->save();

        $game->addInformation(_('Das Forum wurde erstellt'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
