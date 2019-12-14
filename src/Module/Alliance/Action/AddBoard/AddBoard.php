<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\AddBoard;

use Stu\Exception\AccessViolation;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Alliance\View\Boards\Boards;
use Stu\Orm\Repository\AllianceBoardRepositoryInterface;

final class AddBoard implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_ADD_BOARD';

    private AddBoardRequestInterface $addBoardRequest;

    private AllianceBoardRepositoryInterface $allianceBoardRepository;

    private AllianceActionManagerInterface $allianceActionManager;

    public function __construct(
        AddBoardRequestInterface $addBoardRequest,
        AllianceBoardRepositoryInterface $allianceBoardRepository,
        AllianceActionManagerInterface $allianceActionManager
    ) {
        $this->addBoardRequest = $addBoardRequest;
        $this->allianceBoardRepository = $allianceBoardRepository;
        $this->allianceActionManager = $allianceActionManager;
    }

    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();

        $allianceId = (int) $alliance->getId();

        if (!$this->allianceActionManager->mayEdit($allianceId, $game->getUser()->getId())) {
            throw new AccessViolation();
        }

        $game->setView(Boards::VIEW_IDENTIFIER);

        $name = $this->addBoardRequest->getBoardName();

        if (mb_strlen($name) < 5) {
            $game->addInformation(_('Der Name muss mindestens 5 Zeichen lang sein'));
            return;
        }

        $board = $this->allianceBoardRepository->prototype();
        $board->setAlliance($alliance);
        $board->setName($name);

        $this->allianceBoardRepository->save($board);

        $game->addInformation(_('Das Forum wurde erstellt'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
