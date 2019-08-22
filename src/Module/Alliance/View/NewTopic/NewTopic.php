<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\NewTopic;

use AccessViolation;
use AllianceBoard;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;

final class NewTopic implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_NEW_TOPIC';

    private $newTopicRequest;

    public function __construct(
        NewTopicRequestInterface $newTopicRequest
    ) {
        $this->newTopicRequest = $newTopicRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();
        $boardId = $this->newTopicRequest->getBoardId();

        $board = new AllianceBoard($boardId);
        if ($board->getAllianceId() != $alliance->getId()) {
            throw new AccessViolation();
        }

        $game->setPageTitle(_('Allianzforum'));

        $game->appendNavigationPart(
            'alliance.php',
            _('Allianz')
        );
        $game->appendNavigationPart(
            'alliance.php?SHOW_BOARDS=1',
            _('Forum')
        );
        $game->appendNavigationPart(
            sprintf(
                'alliance.php?SHOW_BOARD=1&bid=%d',
                $boardId
            ),
            $board->getName()
        );
        $game->appendNavigationPart(
            sprintf(
                'alliance.php?SHOW_NEW_TOPIC=1&bid=%d',
                $boardId
            ),
            _('Thema erstellen')
        );

        $game->setTemplateFile('html/allianceboardcreatetopic.xhtml');
        $game->setTemplateVar('BOARD_ID', $boardId);
    }
}
