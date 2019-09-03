<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\PostKnComment;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Communication\View\ShowKnComments\ShowKnComments;
use Stu\Orm\Entity\KnPostInterface;
use Stu\Orm\Repository\KnCommentRepositoryInterface;
use Stu\Orm\Repository\KnPostRepositoryInterface;

final class PostKnComment implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_POST_COMMENT';

    private $postKnCommentRequest;

    private $knCommentRepository;

    private $knPostRepository;

    public function __construct(
        PostKnCommentRequestInterface $postKnCommentRequest,
        KnCommentRepositoryInterface $knCommentRepository,
        KnPostRepositoryInterface $knPostRepository
    ) {
        $this->postKnCommentRequest = $postKnCommentRequest;
        $this->knCommentRepository = $knCommentRepository;
        $this->knPostRepository = $knPostRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowKnComments::VIEW_IDENTIFIER);

        /** @var KnPostInterface $post */
        $post = $this->knPostRepository->find($this->postKnCommentRequest->getPostId());

        if ($post === null) {
            return;
        }

        $text = $this->postKnCommentRequest->getText();

        if (mb_strlen($text) < 3) {
            return;
        }
        $obj = $this->knCommentRepository->prototype()
            ->setUserId($game->getUser()->getId())
            ->setDate(time())
            ->setPosting($post)
            ->setText($text);

        $this->knCommentRepository->save($obj);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
