<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\NewPost;

use Override;
use Stu\Exception\AccessViolation;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\AllianceBoardTopicInterface;
use Stu\Orm\Repository\AllianceBoardTopicRepositoryInterface;

final class NewPost implements ViewControllerInterface
{
    /**
     * @var string
     */
    public const string VIEW_IDENTIFIER = 'SHOW_NEW_POST';

    public function __construct(private NewPostRequestInterface $newPostRequest, private AllianceBoardTopicRepositoryInterface $allianceBoardTopicRepository)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();
        $boardId = $this->newPostRequest->getBoardId();
        $topicId = $this->newPostRequest->getTopicId();
        $allianceId = $alliance->getId();

        /** @var AllianceBoardTopicInterface $topic */
        $topic = $this->allianceBoardTopicRepository->find($topicId);
        if ($topic === null || $topic->getAllianceId() !== $allianceId) {
            throw new AccessViolation();
        }

        $board = $topic->getBoard();

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
                'alliance.php?SHOW_TOPIC=1&bid=%d&tid=%d',
                $boardId,
                $topicId
            ),
            $topic->getName()
        );
        $game->appendNavigationPart(
            sprintf(
                'alliance.php?SHOW_NEW_POST=1&bid=%s&tid=%d',
                $boardId,
                $topicId
            ),
            _('Antwort erstellen')
        );

        $game->setTemplateFile('html/allianceboardcreatepost.xhtml');
        $game->setTemplateVar('BOARD_ID', $boardId);
        $game->setTemplateVar('TOPIC', $topic);
    }
}
