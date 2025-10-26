<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\AddBoard;

use Stu\Exception\AccessViolationException;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Alliance\View\Boards\Boards;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\AllianceBoardRepositoryInterface;

/**
 * Adds boards to alliance forums
 */
final class AddBoard implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_ADD_BOARD';

    private const int NAME_LENGTH_CONSTRAINT = 5;

    public function __construct(private AddBoardRequestInterface $addBoardRequest, private AllianceBoardRepositoryInterface $allianceBoardRepository, private AllianceActionManagerInterface $allianceActionManager) {}

    /**
     * @throws AccessViolationException
     */
    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $alliance = $user->getAlliance();

        // throw if user has no alliance
        if ($alliance === null) {
            throw new AccessViolationException();
        }

        // throw if user may not edit alliance
        if (!$this->allianceActionManager->mayEdit($alliance, $user)) {
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
