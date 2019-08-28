<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\DeleteKnComment;

use KnComment;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Communication\View\ShowKnComments\ShowKnComments;

final class DeleteKnComment implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DELETE_COMMENT';

    private $deleteKnCommentRequest;

    public function __construct(
        DeleteKnCommentRequestInterface $deleteKnCommentRequest
    ) {
        $this->deleteKnCommentRequest = $deleteKnCommentRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowKnComments::VIEW_IDENTIFIER);

        $obj = new KnComment($this->deleteKnCommentRequest->getCommentId());
        if ($obj->getPosting()->currentUserMayDeleteComment()) {
            $obj->deleteFromDatabase();
        }


    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
