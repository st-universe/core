<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\CreatePost;

use Stu\Exception\AccessViolation;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Alliance\View\NewPost\NewPost;
use Stu\Module\Alliance\View\Topic\Topic;
use Stu\Orm\Entity\AllianceBoardTopicInterface;
use Stu\Orm\Repository\AllianceBoardPostRepositoryInterface;
use Stu\Orm\Repository\AllianceBoardTopicRepositoryInterface;

final class CreatePost implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_CREATE_POSTING';

    private CreatePostRequestInterface $createPostRequest;

    private AllianceBoardPostRepositoryInterface $allianceBoardPostRepository;

    private AllianceBoardTopicRepositoryInterface $allianceBoardTopicRepository;

    public function __construct(
        CreatePostRequestInterface $createPostRequest,
        AllianceBoardPostRepositoryInterface $allianceBoardPostRepository,
        AllianceBoardTopicRepositoryInterface $allianceBoardTopicRepository
    ) {
        $this->createPostRequest = $createPostRequest;
        $this->allianceBoardPostRepository = $allianceBoardPostRepository;
        $this->allianceBoardTopicRepository = $allianceBoardTopicRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();

        $text = $this->createPostRequest->getText();
        $topicId = $this->createPostRequest->getTopicId();

        if (mb_strlen($text) < 1) {
            $game->setView(NewPost::VIEW_IDENTIFIER);
            $game->addInformation(_('Es wurde kein Text eingegeben'));
            return;
        }

        /** @var AllianceBoardTopicInterface $topic */
        $topic = $this->allianceBoardTopicRepository->find($topicId);
        if ($topic === null || $topic->getAllianceId() != $alliance->getId()) {
            throw new AccessViolation();
        }

        $time = time();
        $topic->setLastPostDate($time);
        $this->allianceBoardTopicRepository->save($topic);

        $post = $this->allianceBoardPostRepository->prototype();
        $post->setText($text);
        $post->setBoard($topic->getBoard());
        $post->setTopic($topic);
        $post->setUser($game->getUser());
        $post->setDate($time);

        $this->allianceBoardPostRepository->save($post);

        $game->setView(Topic::VIEW_IDENTIFIER);

        $game->addInformation(_('Der Beitrag wurde erstellt'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
