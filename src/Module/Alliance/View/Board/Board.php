<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\Board;

use AccessViolation;
use AllianceBoard;
use AllianceTopic;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;

final class Board implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_BOARD';

    private $boardRequest;

    public function __construct(
        BoardRequestInterface $boardRequest
    ) {
        $this->boardRequest = $boardRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();
        $boardId = $this->boardRequest->getBoardId();
        $allianceId = $alliance->getId();
        $board = new AllianceBoard($boardId);

        if ($board->getAllianceId() != $allianceId) {
            throw new AccessViolation();
        }

        $game->setPageTitle(_('Allianzforum'));

        $game->appendNavigationPart(
            sprintf('alliance.php?SHOW_BOARDS=1&id=%d', $boardId),
            _('Allianzforum')
        );
        $game->appendNavigationPart(
            sprintf(
                'alliance.php?SHOW_BOARD=1&bid=%d&id=%d',
                $boardId,
                $allianceId
            ),
            $board->getName()
        );
        $game->setTemplateFile('html/allianceboardtopics.xhtml');
        $game->setTemplateVar(
            'TOPICS',
            AllianceTopic::getList(sprintf(
                'alliance_id = %d AND board_id = %d ORDER BY sticky DESC,last_post_date DESC',
                $allianceId,
                $boardId
            ))
        );
        $game->setTemplateVar(
            'EDITABLE',
            $alliance->currentUserMayEdit()
        );
        $game->setTemplateVar('BOARD_ID', $boardId);
    }
}
