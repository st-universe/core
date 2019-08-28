<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\DeleteBoard;

use AccessViolation;
use AllianceBoard;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Alliance\View\Boards\Boards;

final class DeleteBoard implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_DELETE_BOARD';

    private $deleteBoardRequest;

    public function __construct(
        DeleteBoardRequestInterface $deleteBoardRequest
    ) {
        $this->deleteBoardRequest = $deleteBoardRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();

        $board = new AllianceBoard($this->deleteBoardRequest->getBoardId());
        if ($board->getAllianceId() != $alliance->getId()) {
            throw new AccessViolation();
        }

        $board->deepDelete();

        $game->addInformation(_('Das Forum wurde gelöscht'));

        $game->setView(Boards::VIEW_IDENTIFIER);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
