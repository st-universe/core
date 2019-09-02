<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\DeletePost;

use AccessViolation;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Alliance\View\Board\Board;
use Stu\Module\Alliance\View\Topic\Topic;
use Stu\Orm\Repository\AllianceBoardPostRepositoryInterface;

final class DeletePost implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_DEL_POSTING';

    private $deletePostRequest;

    private $allianceBoardPostRepository;

    public function __construct(
        DeletePostRequestInterface $deletePostRequest,
        AllianceBoardPostRepositoryInterface $allianceBoardPostRepository
    ) {
        $this->deletePostRequest = $deletePostRequest;
        $this->allianceBoardPostRepository = $allianceBoardPostRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();

        $post = $this->allianceBoardPostRepository->find($this->deletePostRequest->getPostId());
        if ($post === null) {
            return;
        }
        if ($post->getAllianceId() != $alliance->getId()) {
            throw new AccessViolation();
        }

        if ($post->getTopic()->getPostCount() == 1) {
            $game->setView(Board::VIEW_IDENTIFIER);

            $post->getTopic()->deepDelete();

            $game->addInformation(_('Das Thema wurde gelöscht'));
            return;
        }
        $game->setView(Topic::VIEW_IDENTIFIER);

        $this->allianceBoardPostRepository->delete($post);

        $game->addInformation(_('Der Beitrag wurde gelöscht'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
