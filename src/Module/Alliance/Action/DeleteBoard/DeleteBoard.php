<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\DeleteBoard;

use Override;
use Stu\Exception\AccessViolationException;
use Stu\Module\Alliance\View\Boards\Boards;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\AllianceBoard;
use Stu\Orm\Repository\AllianceBoardRepositoryInterface;

final class DeleteBoard implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DELETE_BOARD';

    public function __construct(private DeleteBoardRequestInterface $deleteBoardRequest, private AllianceBoardRepositoryInterface $allianceBoardRepository) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();

        $board = $this->allianceBoardRepository->find($this->deleteBoardRequest->getBoardId());
        if ($board === null || $board->getAlliance() !== $alliance) {
            throw new AccessViolationException();
        }

        $this->allianceBoardRepository->delete($board);

        $game->getInfo()->addInformation(_('Das Forum wurde gelöscht'));

        $game->setView(Boards::VIEW_IDENTIFIER);
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
