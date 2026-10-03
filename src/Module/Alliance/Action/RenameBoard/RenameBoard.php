<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\RenameBoard;

use Stu\Exception\AccessViolationException;
use Stu\Module\Alliance\View\Boards\Boards;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Orm\Repository\AllianceBoardRepositoryInterface;

final class RenameBoard implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_RENAME_BOARD';

    public function __construct(
        private RenameBoardRequestInterface $renameBoardRequest,
        private AllianceBoardRepositoryInterface $allianceBoardRepository
    ) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $alliance = $context->getUser()->getAlliance();

        $name = $this->renameBoardRequest->getTitle();

        $board = $this->allianceBoardRepository->find($this->renameBoardRequest->getBoardId());
        if ($board === null || $board->getAlliance()->getId() !== $alliance?->getId()) {
            throw new AccessViolationException();
        }

        $context->setView(Boards::VIEW_IDENTIFIER);

        if (mb_strlen($name) < 1) {
            $context->getInfo()->addInformation(_('Es wurde kein Forumname eingegeben'));
            return;
        }

        $board->setName($name);

        $this->allianceBoardRepository->save($board);

        $context->getInfo()->addInformation(_('Das Forum wurde umbenannt'));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
