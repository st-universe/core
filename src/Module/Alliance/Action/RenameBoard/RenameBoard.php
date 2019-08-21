<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\RenameBoard;

use AccessViolation;
use AllianceBoard;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
use Stu\Module\Alliance\View\Boards\Boards;

final class RenameBoard implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_RENAME_BOARD';

    private $renameBoardRequest;

    public function __construct(
        RenameBoardRequestInterface $renameBoardRequest
    ) {
        $this->renameBoardRequest = $renameBoardRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();

        $name = $this->renameBoardRequest->getTitle();
        $boardId = $this->renameBoardRequest->getBoardId();

        $board = new AllianceBoard($boardId);
        if ($board->getAllianceId() != $alliance->getId()) {
            throw new AccessViolation();
        }

        $game->setView(Boards::VIEW_IDENTIFIER);

        if (mb_strlen($name) < 1) {
            $game->addInformation(_('Es wurde kein Forumname eingegeben'));
            return;
        }

        $board->setName($name);
        $board->save();

        $game->addInformation(_('Das Forum wurde umbenannt'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
