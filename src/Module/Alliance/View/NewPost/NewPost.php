<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\NewPost;

use AccessViolation;
use AllianceBoard;
use AllianceTopic;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;
use Stu\Module\Alliance\View\NewPost\NewPostRequestInterface;

final class NewPost implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_NEW_POST';

    private $newPostRequest;

    public function __construct(
        NewPostRequestInterface $newPostRequest
    ) {
        $this->newPostRequest = $newPostRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();
        $boardId = $this->newPostRequest->getBoardId();
        $topicId = $this->newPostRequest->getTopicId();
        $allianceId = $alliance->getId();

        $board = new AllianceBoard($boardId);
        if ($board->getAllianceId() != $alliance->getId()) {
            throw new AccessViolation();
        }

        $topic = new AllianceTopic($topicId);
        if ($topic->getAllianceId() != $allianceId) {
            throw new AccessViolation();
        }

        $game->setPageTitle(_('Allianzforum'));

        $game->appendNavigationPart(
            sprintf('alliance.php?SHOW_BOARDS=1&id=%d', $allianceId),
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
        $game->appendNavigationPart(
            sprintf(
                'alliance.php?SHOW_NEW_POST=1&bid=%s&id=%s',
                $boardId,
                $allianceId
            ),
            _('Antwort erstellen')
        );

        $game->setTemplateFile('html/allianceboardcreatepost.xhtml');
        $game->setTemplateVar('BOARD_ID', $boardId);
        $game->setTemplateVar('TOPIC', $topic);
    }
}
