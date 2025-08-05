<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\CreatePost;

use Override;
use Stu\Exception\AccessViolationException;
use Stu\Module\Alliance\View\NewPost\NewPost;
use Stu\Module\Alliance\View\Topic\Topic;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\AllianceBoardPostRepositoryInterface;
use Stu\Orm\Repository\AllianceBoardTopicRepositoryInterface;

final class CreatePost implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_CREATE_POSTING';

    public function __construct(
        private CreatePostRequestInterface $createPostRequest,
        private AllianceBoardPostRepositoryInterface $allianceBoardPostRepository,
        private AllianceBoardTopicRepositoryInterface $allianceBoardTopicRepository
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();

        $text = $this->createPostRequest->getText();
        $topicId = $this->createPostRequest->getTopicId();

        if (mb_strlen($text) < 1) {
            $game->setView(NewPost::VIEW_IDENTIFIER);
            $game->getInfo()->addInformation(_('Es wurde kein Text eingegeben'));
            return;
        }

        $topic = $this->allianceBoardTopicRepository->find($topicId);
        if ($topic === null || $topic->getAlliance() !== $alliance) {
            throw new AccessViolationException();
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

        $game->getInfo()->addInformation(_('Der Beitrag wurde erstellt'));
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
