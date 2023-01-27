<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\AddBoard;

use Stu\Exception\AccessViolation;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Alliance\View\Boards\Boards;
use Stu\Orm\Repository\AllianceBoardRepositoryInterface;

/**
 * Adds boards to alliance forums
 */
final class AddBoard implements ActionControllerInterface
{
    /** @var string */
    public const ACTION_IDENTIFIER = 'B_ADD_BOARD';

    /** @var int */
    private const NAME_LENGTH_CONSTRAINT = 5;

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

    /**
     * @throws AccessViolation
     */
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $alliance = $user->getAlliance();

        // throw if user has no alliance
        if ($alliance === null) {
            throw new AccessViolation();
        }

        $allianceId = $alliance->getId();

        // throw if user may not edit alliance
        if (!$this->allianceActionManager->mayEdit($allianceId, $user->getId())) {
            throw new AccessViolation();
        }

        $game->setView(Boards::VIEW_IDENTIFIER);

        $name = $this->addBoardRequest->getBoardName();

        if (mb_strlen($name) < self::NAME_LENGTH_CONSTRAINT) {
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
