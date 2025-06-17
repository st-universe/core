<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\RenameBoard;

use Override;
use Stu\Exception\AccessViolation;
use Stu\Module\Alliance\View\Boards\Boards;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\AllianceBoardInterface;
use Stu\Orm\Repository\AllianceBoardRepositoryInterface;

final class RenameBoard implements ActionControllerInterface
{
    /**
     * @var string
     */
    public const string ACTION_IDENTIFIER = 'B_RENAME_BOARD';

    public function __construct(private RenameBoardRequestInterface $renameBoardRequest, private AllianceBoardRepositoryInterface $allianceBoardRepository)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();

        $name = $this->renameBoardRequest->getTitle();

        /** @var AllianceBoardInterface $board */
        $board = $this->allianceBoardRepository->find($this->renameBoardRequest->getBoardId());
        if ($board === null || $board->getAllianceId() !== $alliance->getId()) {
            throw new AccessViolation();
        }

        $game->setView(Boards::VIEW_IDENTIFIER);

        if (mb_strlen($name) < 1) {
            $game->addInformation(_('Es wurde kein Forumname eingegeben'));
            return;
        }

        $board->setName($name);

        $this->allianceBoardRepository->save($board);

        $game->addInformation(_('Das Forum wurde umbenannt'));
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
