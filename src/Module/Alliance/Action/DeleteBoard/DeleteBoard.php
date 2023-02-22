<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\DeleteBoard;

use Stu\Exception\AccessViolation;
use Stu\Module\Alliance\View\Boards\Boards;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\AllianceBoardInterface;
use Stu\Orm\Repository\AllianceBoardRepositoryInterface;

final class DeleteBoard implements ActionControllerInterface
{
    /**
     * @var string
     */
    public const ACTION_IDENTIFIER = 'B_DELETE_BOARD';

    private DeleteBoardRequestInterface $deleteBoardRequest;

    private AllianceBoardRepositoryInterface $allianceBoardRepository;

    public function __construct(
        DeleteBoardRequestInterface $deleteBoardRequest,
        AllianceBoardRepositoryInterface $allianceBoardRepository
    ) {
        $this->deleteBoardRequest = $deleteBoardRequest;
        $this->allianceBoardRepository = $allianceBoardRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();

        /** @var AllianceBoardInterface $board */
        $board = $this->allianceBoardRepository->find($this->deleteBoardRequest->getBoardId());
        if ($board === null || $board->getAllianceId() !== $alliance->getId()) {
            throw new AccessViolation();
        }

        $this->allianceBoardRepository->delete($board);

        $game->addInformation(_('Das Forum wurde gelöscht'));

        $game->setView(Boards::VIEW_IDENTIFIER);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
