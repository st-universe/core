<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\DeleteKnComment;

use Stu\Module\Communication\View\ShowKnComments\ShowKnComments;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\KnCommentRepositoryInterface;

final class DeleteKnComment implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DELETE_COMMENT';

    public function __construct(private DeleteKnCommentRequestInterface $deleteKnCommentRequest, private KnCommentRepositoryInterface $knCommentRepository) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $game->setView(ShowKnComments::VIEW_IDENTIFIER);

        $obj = $this->knCommentRepository->find($this->deleteKnCommentRequest->getCommentId());
        if ($obj === null) {
            return;
        }

        if ($obj->getUserId() === $userId) {
            $obj->setDeleted(time());
            $this->knCommentRepository->save($obj);
        }
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
