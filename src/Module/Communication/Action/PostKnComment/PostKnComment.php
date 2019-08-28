<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\PostKnComment;

use KnCommentData;
use KNPosting;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Communication\View\ShowKnComments\ShowKnComments;

final class PostKnComment implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_POST_COMMENT';

    private $postKnCommentRequest;

    public function __construct(
        PostKnCommentRequestInterface $postKnCommentRequest
    ) {
        $this->postKnCommentRequest = $postKnCommentRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowKnComments::VIEW_IDENTIFIER);

        $post = new KNPosting($this->postKnCommentRequest->getPostId());
        $text = $this->postKnCommentRequest->getText();

        if (mb_strlen($text) < 3) {
            return;
        }
        $obj = new KnCommentData;
        $obj->setUserId($game->getUser()->getId());
        $obj->setDate(time());
        $obj->setPostId($post->getId());
        $obj->setText($text);
        $obj->save();
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
