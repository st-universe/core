<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\DeleteKnComment;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Communication\View\ShowKnComments\ShowKnComments;
use Stu\Orm\Entity\KnCommentInterface;
use Stu\Orm\Repository\KnCommentRepositoryInterface;

final class DeleteKnComment implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DELETE_COMMENT';

    private DeleteKnCommentRequestInterface $deleteKnCommentRequest;

    private KnCommentRepositoryInterface $knCommentRepository;

    public function __construct(
        DeleteKnCommentRequestInterface $deleteKnCommentRequest,
        KnCommentRepositoryInterface $knCommentRepository
    ) {
        $this->deleteKnCommentRequest = $deleteKnCommentRequest;
        $this->knCommentRepository = $knCommentRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $game->setView(ShowKnComments::VIEW_IDENTIFIER);

        /** @var KnCommentInterface $obj */
        $obj = $this->knCommentRepository->find($this->deleteKnCommentRequest->getCommentId());
        if ($obj === null) {
            return;
        }

        if ($obj->getUserId() == $userId) {
            $obj->setDeleted(time());
            $this->knCommentRepository->save($obj);
        }
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
