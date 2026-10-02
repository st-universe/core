<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\CreatePost;

use Stu\Exception\AccessViolationException;
use Stu\Module\Alliance\View\NewPost\NewPost;
use Stu\Module\Alliance\View\Topic\Topic;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
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

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $alliance = $context->getUser()->getAlliance();

        $text = $this->createPostRequest->getText();
        $topicId = $this->createPostRequest->getTopicId();

        if (mb_strlen($text) < 1) {
            $context->setView(NewPost::VIEW_IDENTIFIER);
            $context->getInfo()->addInformation(_('Es wurde kein Text eingegeben'));
            return;
        }

        $topic = $this->allianceBoardTopicRepository->find($topicId);
        if ($topic === null || $topic->getAlliance()->getId() !== $alliance?->getId()) {
            throw new AccessViolationException();
        }

        $time = time();
        $topic->setLastPostDate($time);
        $this->allianceBoardTopicRepository->save($topic);

        $post = $this->allianceBoardPostRepository->prototype();
        $post->setText($text);
        $post->setBoard($topic->getBoard());
        $post->setTopic($topic);
        $post->setUser($context->getUser());
        $post->setDate($time);

        $this->allianceBoardPostRepository->save($post);

        $context->setView(Topic::VIEW_IDENTIFIER);

        $context->getInfo()->addInformation(_('Der Beitrag wurde erstellt'));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
