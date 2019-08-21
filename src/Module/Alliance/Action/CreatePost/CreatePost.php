<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\CreatePost;

use AccessViolation;
use AllianceBoard;
use AlliancePostData;
use AllianceTopic;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
use Stu\Module\Alliance\View\NewPost\NewPost;
use Stu\Module\Alliance\View\Topic\Topic;

final class CreatePost implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_CREATE_POSTING';

    private $createPostRequest;

    public function __construct(
        CreatePostRequestInterface $createPostRequest
    ) {
        $this->createPostRequest = $createPostRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();
        $userId = $game->getUser()->getId();

        $text = $this->createPostRequest->getText();
        $boardId = $this->createPostRequest->getBoardId();
        $topicId = $this->createPostRequest->getTopicId();

        if (mb_strlen($text) < 1) {
            $game->setView(NewPost::VIEW_IDENTIFIER);
            $game->addInformation(_('Es wurde kein Text eingegeben'));
            return;
        }

        $board = new AllianceBoard($boardId);
        if ($board->getAllianceId() != $alliance->getId()) {
            throw new AccessViolation();
        }
        $topic = new AllianceTopic($topicId);
        if ($topic->getAllianceId() != $alliance->getId()) {
            throw new AccessViolation();
        }

        $post = new AlliancePostData();
        $post->setText($text);
        $post->setBoardId($board->getId());
        $post->setTopicId($topic->getId());
        $post->setAllianceId($alliance->getId());
        $post->setUserId($userId);
        $post->setDate(time());
        $post->save();

        $game->setView(Topic::VIEW_IDENTIFIER);

        $game->addInformation(_('Der Beitrag wurde erstellt'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
