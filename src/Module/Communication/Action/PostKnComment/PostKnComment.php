<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\PostKnComment;

use KNPosting;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Communication\View\ShowKnComments\ShowKnComments;
use Stu\Orm\Repository\KnCommentRepositoryInterface;

final class PostKnComment implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_POST_COMMENT';

    private $postKnCommentRequest;

    private $knCommentRepository;

    public function __construct(
        PostKnCommentRequestInterface $postKnCommentRequest,
        KnCommentRepositoryInterface $knCommentRepository
    ) {
        $this->postKnCommentRequest = $postKnCommentRequest;
        $this->knCommentRepository = $knCommentRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowKnComments::VIEW_IDENTIFIER);

        $post = new KNPosting($this->postKnCommentRequest->getPostId());
        $text = $this->postKnCommentRequest->getText();

        if (mb_strlen($text) < 3) {
            return;
        }
        $obj = $this->knCommentRepository->prototype()
            ->setUserId($game->getUser()->getId())
            ->setDate(time())
            ->setPostId((int) $post->getId())
            ->setText($text);

        $this->knCommentRepository->save($obj);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
