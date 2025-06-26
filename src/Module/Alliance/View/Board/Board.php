<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\Board;

use Override;
use Stu\Exception\AccessViolationException;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\AllianceBoard;
use Stu\Orm\Repository\AllianceBoardRepositoryInterface;
use Stu\Orm\Repository\AllianceBoardTopicRepositoryInterface;

final class Board implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_BOARD';

    public function __construct(private BoardRequestInterface $boardRequest, private AllianceBoardTopicRepositoryInterface $allianceBoardTopicRepository, private AllianceBoardRepositoryInterface $allianceBoardRepository, private AllianceActionManagerInterface $allianceActionManager) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();

        if ($alliance === null) {
            throw new AccessViolationException("user not in alliance");
        }

        $allianceId = $alliance->getId();

        $board = $this->allianceBoardRepository->find($this->boardRequest->getBoardId());
        if ($board === null || $board->getAllianceId() !== $allianceId) {
            throw new AccessViolationException();
        }

        $boardId = $board->getId();

        $game->setPageTitle(_('Allianzforum'));

        $game->appendNavigationPart(
            'alliance.php',
            _('Allianz')
        );
        $game->appendNavigationPart(
            sprintf('alliance.php?SHOW_BOARDS=1&id=%d', $boardId),
            _('Forum')
        );
        $game->appendNavigationPart(
            sprintf(
                'alliance.php?SHOW_BOARD=1&boardid=%d',
                $boardId,
            ),
            $board->getName()
        );
        $game->setViewTemplate('html/alliance/allianceboardtopics.twig');
        $game->setTemplateVar(
            'TOPICS',
            $this->allianceBoardTopicRepository->getByBoardIdOrdered($boardId)
        );
        $game->setTemplateVar(
            'EDITABLE',
            $this->allianceActionManager->mayEdit($alliance, $game->getUser())
        );
        $game->setTemplateVar('BOARD_ID', $boardId);
    }
}
