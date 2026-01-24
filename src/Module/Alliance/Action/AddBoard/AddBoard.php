<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\AddBoard;

use Stu\Component\Alliance\Enum\AllianceJobPermissionEnum;
use Stu\Exception\AccessViolationException;
use Stu\Module\Alliance\Lib\AllianceJobManagerInterface;
use Stu\Module\Alliance\View\Boards\Boards;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\AllianceBoardRepositoryInterface;

final class AddBoard implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_ADD_BOARD';

    private const int NAME_LENGTH_CONSTRAINT = 5;

    public function __construct(
        private AddBoardRequestInterface $addBoardRequest,
        private AllianceBoardRepositoryInterface $allianceBoardRepository,
        private AllianceJobManagerInterface $allianceJobManager
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $alliance = $user->getAlliance();

        if ($alliance === null) {
            throw new AccessViolationException();
        }

        if (!$this->allianceJobManager->hasUserPermission($user, $alliance, AllianceJobPermissionEnum::EDIT_ALLIANCE)) {
            throw new AccessViolationException();
        }

        $game->setView(Boards::VIEW_IDENTIFIER);

        $name = $this->addBoardRequest->getBoardName();

        if (mb_strlen($name) < self::NAME_LENGTH_CONSTRAINT) {
            $game->getInfo()->addInformation(_('Der Name muss mindestens 5 Zeichen lang sein'));
            return;
        }

        $board = $this->allianceBoardRepository->prototype();
        $board->setAlliance($alliance);
        $board->setName($name);

        $this->allianceBoardRepository->save($board);

        $game->getInfo()->addInformation(_('Das Forum wurde erstellt'));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
